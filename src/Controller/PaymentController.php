<?php
// src/Controller/PaymentController.php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Service\OrderService;
use App\Service\StripeConfig;
use App\Service\StripeSessionFactory;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/payment')]
#[IsGranted('ROLE_USER')]
class PaymentController extends AbstractController
{
    private StripeConfig $stripeConfig;
    private OrderService $orderService;
    private LoggerInterface $logger;
    private CartService $cartService;
    private StripeSessionFactory $stripeSessionFactory;

    public function __construct(
        private EntityManagerInterface $entityManager,
        StripeConfig $stripeConfig,
        OrderService $orderService,
        CartService $cartService,
        LoggerInterface $logger,
        StripeSessionFactory $stripeSessionFactory
    ) {
        $this->stripeConfig = $stripeConfig;
        $this->orderService = $orderService;
        $this->cartService = $cartService;
        $this->logger = $logger;
        $this->stripeSessionFactory = $stripeSessionFactory;
    }

    /**
     * Affiche la page de confirmation de commande avec le panier et la clé publique Stripe.
     */
    #[Route('/checkout-page', name: 'checkout_page', methods: ['GET'])]
    public function renderCheckoutPage(): Response
    {
        $stripePublicKey = $this->stripeConfig->getPublicKey();

        // Récupérer les données du panier
        $cart = $this->cartService->getCart();
        $cartData = [];
        $total = 0;

        foreach ($cart as $productId => $quantity) {
            $product = $this->entityManager->getRepository(Product::class)->find($productId);
            if ($product) {
                $subtotal = $product->getPrice() * $quantity;
                $total += $subtotal;

                $cartData[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                ];
            }
        }

        return $this->render('cart/checkout.html.twig', [
            'stripe_public_key' => $stripePublicKey,
            'cart' => $cartData,
            'total' => $total,
        ]);
    }

    /**
     * Crée une session de paiement Stripe et renvoie l'ID de session.
     */
    #[Route('/checkout', name: 'checkout', methods: ['POST'])]
    public function checkout(Request $request): JsonResponse
    {
        if (!$this->isCsrfTokenValid('checkout', $request->request->get('_token'))) {
            return new JsonResponse(['error' => 'Token CSRF invalide.'], 400);
        }

        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé ou non connecté.'], 400);
        }

        $cart = $this->cartService->getCart();

        if (empty($cart)) {
            return new JsonResponse(['error' => 'Votre panier est vide.'], 400);
        }
        Stripe::setApiKey($this->stripeConfig->getSecretKey());
        $lineItems = [];

        foreach ($cart as $productId => $quantity) {
            $product = $this->entityManager->getRepository(Product::class)->find($productId);
            if (!$product) continue;

            if ($product->getStockQuantity() < $quantity) {
                return new JsonResponse(['error' => 'Le produit ' . $product->getName() . ' n\'a plus assez de stock.'], 400);
            }

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $product->getName(),
                    ],
                    'unit_amount' => (int)($product->getPrice() * 100), // Convertir en centimes et caster en int
                ],
                'quantity' => $quantity,
            ];
        }

        if (empty($lineItems)) {
            return new JsonResponse(['error' => 'Votre panier est vide ou contient des produits invalides.'], 400);
        }

        try {
            $checkoutSession = $this->stripeSessionFactory->create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => $this->generateUrl('payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $this->generateUrl('payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'metadata' => [
                    'user_id' => $user->getId(),
                ],
            ]);

            return new JsonResponse(['id' => $checkoutSession->id]);

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création de la session de paiement : ' . $e->getMessage());
            return new JsonResponse(['error' => 'Une erreur est survenue lors de l\'initialisation du paiement.'], 500);
        }
    }

    /**
     * Gère le succès du paiement.
     */
    #[Route('/success', name: 'payment_success')]
    public function success(Request $request): Response
    {
        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            $this->addFlash('error', 'Session de paiement invalide.');
            return $this->redirectToRoute('shop_index');
        }

        Stripe::setApiKey($this->stripeConfig->getSecretKey());

        try {
            $session = StripeSession::retrieve($sessionId);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération de la session Stripe : ' . $e->getMessage());
            $this->addFlash('error', 'Erreur lors de la récupération de la session de paiement.');
            return $this->redirectToRoute('shop_index');
        }

        /** @var User|null $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('shop_index');
        }

        try {
            // Créer une commande à partir du panier en session
            $this->orderService->createOrderFromSessionCart($user, $sessionId);
            // Vider le panier
            $this->cartService->clearCart();
            $this->addFlash('success', 'Votre paiement a été effectué avec succès.');
            return $this->redirectToRoute('shop_index');
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors du traitement de la commande : ' . $e->getMessage());
            $this->addFlash('error', 'Une erreur est survenue lors du traitement de votre commande.');
            return $this->redirectToRoute('view_cart');
        }
    }

    /**
     * Gère l'annulation du paiement.
     */
    #[Route('/cancel', name: 'payment_cancel')]
    public function cancel(): Response
    {
        $this->addFlash('error', 'Le paiement a été annulé.');
        return $this->redirectToRoute('view_cart');
    }

    /**
     * Gère le webhook Stripe.
     */
    #[Route('/stripe/webhook', name: 'stripe_webhook')]
    public function webhook(Request $request, OrderService $orderService): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('stripe-signature');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $this->stripeConfig->getWebhookSecret()
            );
        } catch (\UnexpectedValueException $e) {
            $this->logger->error('Invalid webhook payload', ['exception' => $e]);
            return new Response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            $this->logger->error('Invalid webhook signature', ['exception' => $e]);
            return new Response('Invalid signature', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            // Vérifiez si la commande a déjà été traitée
            $orderExists = $this->entityManager->getRepository(Order::class)
                ->findOneBy(['stripeSessionId' => $session->id]);

            if ($orderExists) {
                return new Response('Order already processed', 200);
            }

            $userId = $session->metadata->user_id ?? null;

            if (!$userId) {
                $this->logger->error('User ID not found in session metadata.');
                return new Response('User ID not found', 400);
            }

            $user = $this->entityManager->getRepository(User::class)->find($userId);

            if (!$user) {
                $this->logger->error('User not found for session ID: ' . $session->id);
                return new Response('User not found', 400);
            }

            try {
                // Créer une commande à partir du panier en session
                $order = $orderService->createOrderFromSessionCart($user, $session->id);
                return new Response('Webhook handled', 200);
            } catch (\Exception $e) {
                $this->logger->error('Erreur lors du traitement de la commande via webhook : ' . $e->getMessage());
                return new Response('Webhook processing error', 500);
            }
        }

        return new Response('Webhook handled', 200);
    }
}
