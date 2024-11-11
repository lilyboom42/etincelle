<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderLine;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\OrderStatus;
use App\Service\StripeConfig;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    private StripeConfig $stripeConfig;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RequestStack $requestStack,
        private readonly LoggerInterface $logger,
        StripeConfig $stripeConfig
    ) {
        $this->stripeConfig = $stripeConfig;
    }

    private function getCart(): array
    {
        return $this->requestStack->getSession()->get('cart', []);
    }

    private function saveCart(array $cart): void
    {
        $this->requestStack->getSession()->set('cart', $cart);
    }

    #[Route('/checkout-page', name: 'checkout_page', methods: ['GET'])]
    public function renderCheckoutPage(): Response
    {
        $stripePublicKey = $this->stripeConfig->getPublicKey();

        return $this->render('cart/checkout.html.twig', [
            'stripe_public_key' => $stripePublicKey,
        ]);
    }

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

        $cart = $this->getCart();
        if (!$cart || count($cart) === 0) {
            return new JsonResponse(['error' => 'Votre panier est vide.'], 400);
        }

        Stripe::setApiKey($this->stripeConfig->getSecretKey());
        $lineItems = [];

        foreach ($cart as $productId => $details) {
            $product = $this->entityManager->getRepository(Product::class)->find($productId);
            if (!$product) continue;

            $quantity = $details['quantity'];

            if ($product->getStockQuantity() < $quantity) {
                return new JsonResponse(['error' => 'Le produit ' . $product->getName() . ' n\'a plus assez de stock.'], 400);
            }

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $product->getName(),
                    ],
                    'unit_amount' => $product->getPrice() * 100,
                ],
                'quantity' => $quantity,
            ];
        }

        if (empty($lineItems)) {
            return new JsonResponse(['error' => 'Votre panier est vide ou contient des produits invalides.'], 400);
        }

        try {
            $checkoutSession = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'metadata' => [
                    'user_id' => $user->getId(),
                    'cart' => json_encode($cart)
                ],
                'success_url' => $this->generateUrl('payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $this->generateUrl('payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

            return new JsonResponse(['id' => $checkoutSession->id]);

        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création de la session de paiement : ' . $e->getMessage());
            return new JsonResponse(['error' => 'Une erreur est survenue lors de l\'initialisation du paiement.'], 500);
        }
    }

    #[Route('/payment-success', name: 'payment_success')]
    #[IsGranted('ROLE_USER')]
    public function success(Request $request): Response
    {
        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            $this->logger->error('Session de paiement invalide: aucun session_id fourni.');
            $this->addFlash('error', 'Session de paiement invalide.');
            return $this->redirectToRoute('shop_index');
        }

        $this->logger->info('Session ID reçu: ' . $sessionId);

        Stripe::setApiKey($this->stripeConfig->getSecretKey());

        try {
            $session = StripeSession::retrieve($sessionId);
        } catch (\Exception $e) {
            $this->logger->error('Impossible de récupérer la session de paiement : ' . $e->getMessage());
            $this->addFlash('error', 'Impossible de récupérer la session de paiement : ' . $e->getMessage());
            return $this->redirectToRoute('shop_index');
        }

        $orderExists = $this->entityManager->getRepository(Order::class)
            ->findOneBy(['stripeSessionId' => $sessionId]);

        if ($orderExists) {
            $this->addFlash('info', 'Cette commande a déjà été traitée.');
            return $this->redirectToRoute('shop_index');
        }

        $userId = $session->metadata->user_id ?? null;
        $cart = json_decode($session->metadata->cart, true);

        if (!$userId || !$cart) {
            $this->addFlash('error', 'Données de paiement invalides.');
            return $this->redirectToRoute('shop_index');
        }

        $user = $this->entityManager->getRepository(User::class)->find($userId);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('shop_index');
        }

        $this->entityManager->getConnection()->beginTransaction();

        try {
            $order = new Order();
            $order->setUser($user);
            $order->setOrderStatus(OrderStatus::COMPLETED);
            $order->setStripeSessionId($sessionId);

            $total = 0;

            foreach ($cart as $productId => $details) {
                $product = $this->entityManager->getRepository(Product::class)->find($productId);

                if (!$product) {
                    continue;
                }

                $quantity = $details['quantity'];
                $unitAmount = $product->getPrice();
                $subtotal = $unitAmount * $quantity;
                $total += $subtotal;

                $orderLine = new OrderLine();
                $orderLine->setOrder($order);
                $orderLine->setProduct($product);
                $orderLine->setQuantity($quantity);
                $orderLine->setPrice($unitAmount);

                $order->addOrderLine($orderLine);
                $product->decrementStockQuantity($quantity);
                $this->entityManager->persist($product);
            }

            $order->setTotal($total);
            $this->entityManager->persist($order);
            $this->entityManager->flush();

            $this->entityManager->getConnection()->commit();
            $this->saveCart([]);
            $this->addFlash('success', 'Votre paiement a été effectué avec succès !');

            return $this->redirectToRoute('shop_index');

        } catch (\Exception $e) {
            $this->entityManager->getConnection()->rollBack();
            $this->logger->error('Erreur lors du traitement de la commande : ' . $e->getMessage());
            $this->addFlash('error', 'Une erreur est survenue lors du traitement de votre commande. Veuillez réessayer.');
            return $this->redirectToRoute('view_cart');
        }
    }

    #[Route('/payment-cancel', name: 'payment_cancel')]
    public function cancel(): Response
    {
        $this->addFlash('error', 'Le paiement a été annulé.');
        return $this->redirectToRoute('view_cart');
    }

    #[Route('/stripe/webhook', name: 'stripe_webhook')]
    public function webhook(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('stripe-signature');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $this->stripeConfig->getWebhookSecret());
        } catch (\UnexpectedValueException $e) {
            $this->logger->error('Invalid webhook payload', ['exception' => $e]);
            return new Response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            $this->logger->error('Invalid webhook signature', ['exception' => $e]);
            return new Response('Invalid signature', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            $orderExists = $this->entityManager->getRepository(Order::class)
                ->findOneBy(['stripeSessionId' => $session->id]);

            if ($orderExists) {
                return new Response('Order already processed', 200);
            }

            $userId = $session->metadata->user_id ?? null;
            $cart = json_decode($session->metadata->cart, true);

            if (!$userId || !$cart) {
                return new Response('User ID or cart data not found in metadata', 400);
            }

            $user = $this->entityManager->getRepository(User::class)->find($userId);

            if (!$user) {
                return new Response('User not found', 400);
            }

            $this->entityManager->getConnection()->beginTransaction();

            try {
                $order = new Order();
                $order->setUser($user);
                $order->setOrderStatus(OrderStatus::COMPLETED);
                $order->setStripeSessionId($session->id);

                $total = 0;

                foreach ($cart as $productId => $details) {
                    $product = $this->entityManager->getRepository(Product::class)->find($productId);

                    if (!$product) {
                        continue;
                    }

                    $quantity = $details['quantity'];
                    $unitAmount = $product->getPrice();
                    $subtotal = $unitAmount * $quantity;
                    $total += $subtotal;

                    $orderLine = new OrderLine();
                    $orderLine->setOrder($order);
                    $orderLine->setProduct($product);
                    $orderLine->setQuantity($quantity);
                    $orderLine->setPrice($unitAmount);

                    $order->addOrderLine($orderLine);
                    $product->decrementStockQuantity($quantity);
                    $this->entityManager->persist($product);
                }

                $order->setTotal($total);
                $this->entityManager->persist($order);
                $this->entityManager->flush();

                $this->entityManager->getConnection()->commit();

            } catch (\Exception $e) {
                $this->entityManager->getConnection()->rollBack();
                $this->logger->error('Erreur lors du traitement de la commande via webhook : ' . $e->getMessage());
                return new Response('Webhook processing error', 500);
            }
        }

        return new Response('Webhook handled', 200);
    }

    #[Route('/payment/page', name: 'payment_page', methods: ['GET'])]
public function paymentPage(): Response
{
    return $this->render('payment/index.html.twig'); 
}


}
