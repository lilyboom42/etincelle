<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderLine;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly string $stripeSecretKey,
        private readonly RequestStack $requestStack,
        private readonly string $stripeWebhookSecret,
        private readonly LoggerInterface $logger
    ) {}

    private function getCart(): array
    {
        return $this->requestStack->getSession()->get('cart', []);
    }

    private function saveCart(array $cart): void
    {
        $this->requestStack->getSession()->set('cart', $cart);
    }

    #[Route('/checkout', name: 'checkout', methods: ['POST'])]
    public function checkout(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('checkout', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        /** @var User $user */
        $user = $this->getUser();

        $cart = $this->getCart();

        if (!$cart || count($cart) === 0) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('view_cart');
        }

        Stripe::setApiKey($this->stripeSecretKey);

        $lineItems = [];
        foreach ($cart as $productId => $details) {
            $product = $this->entityManager->getRepository(Product::class)->find($productId);

            if (!$product) {
                continue;
            }

            $quantity = $details['quantity'];

            // Vérification du stock avant de créer la session de paiement
            if ($product->getStockQuantity() < $quantity) {
                $this->addFlash('error', 'Le produit ' . $product->getName() . ' n\'a plus assez de stock.');
                return $this->redirectToRoute('view_cart'); // Redirige l'utilisateur si le stock est insuffisant
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
            $this->addFlash('error', 'Votre panier est vide ou contient des produits invalides.');
            return $this->redirectToRoute('view_cart');
        }

        try {
            $checkoutSession = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'metadata' => [
                    'user_id' => $user->getId(),
                    'cart' => json_encode($cart)  // Stocker le panier complet dans les métadonnées
                ],
                'success_url' => $this->generateUrl('payment_success', ['session_id' => '{CHECKOUT_SESSION_ID}'], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création de la session de paiement : ' . $e->getMessage());
            $this->addFlash('error', 'Une erreur est survenue lors de l\'initialisation du paiement : ' . $e->getMessage());
            return $this->redirectToRoute('view_cart');
        }

        return $this->redirect($checkoutSession->url, 303);
    }

    #[Route('/payment-success', name: 'payment_success')]
    #[IsGranted('ROLE_USER')]
    public function success(Request $request): Response
    {
        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            $this->addFlash('error', 'Session de paiement invalide.');
            return $this->redirectToRoute('shop_index');
        }

        Stripe::setApiKey($this->stripeSecretKey);

        try {
            $session = StripeSession::retrieve($sessionId);
        } catch (\Exception $e) {
            $this->logger->error('Impossible de récupérer la session de paiement : ' . $e->getMessage());
            $this->addFlash('error', 'Impossible de récupérer la session de paiement : ' . $e->getMessage());
            return $this->redirectToRoute('shop_index');
        }

        // Vérifier si une commande existe déjà pour cette session
        $orderExists = $this->entityManager->getRepository(Order::class)
            ->findOneBy(['stripeSessionId' => $sessionId]);

        if ($orderExists) {
            $this->addFlash('info', 'Cette commande a déjà été traitée.');
            return $this->redirectToRoute('shop_index');
        }

        // Récupérer les métadonnées et le panier
        $userId = $session->metadata->user_id;
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

        // Créer la commande
        $order = new Order();
        $order->setUser($user);
        $order->setOrderStatus(OrderStatus::COMPLETED);
        $order->setStripeSessionId($sessionId);

        $total = 0;

        // Parcourir le panier stocké dans les métadonnées
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

            // Décrémenter le stock du produit
            try {
                $product->decrementStockQuantity($quantity);
            } catch (\Exception $e) {
                $this->logger->error('Stock insuffisant pour le produit : ' . $product->getName());
                $this->addFlash('error', 'Stock insuffisant pour le produit : ' . $product->getName());
                return $this->redirectToRoute('view_cart');
            }

            $this->entityManager->persist($product); // Mettre à jour le produit dans la base de données
        }

        $order->setTotal($total);

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        // Vider le panier de la session (si vous le souhaitez)
        $this->saveCart([]);

        $this->addFlash('success', 'Votre paiement a été effectué avec succès !');

        return $this->redirectToRoute('shop_index');
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
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $this->stripeWebhookSecret);
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

            $userId = $session->metadata->user_id;
            $cart = json_decode($session->metadata->cart, true);

            if (!$userId || !$cart) {
                return new Response('User ID or cart data not found in metadata', 400);
            }

            $user = $this->entityManager->getRepository(User::class)->find($userId);

            if (!$user) {
                return new Response('User not found', 400);
            }

            // Créer la commande
            $order = new Order();
            $order->setUser($user);
            $order->setOrderStatus(OrderStatus::COMPLETED);
            $order->setStripeSessionId($session->id);

            $total = 0;

            // Parcourir le panier stocké dans les métadonnées
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
            }

            $order->setTotal($total);
            $this->entityManager->persist($order);
            $this->entityManager->flush();
        }

        return new Response('Webhook handled', 200);
    }
}
