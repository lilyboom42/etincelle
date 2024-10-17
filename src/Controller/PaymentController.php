<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderLine;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


#[Route('/payment')]
class PaymentController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly string $stripeSecretKey,
        private readonly RequestStack $requestStack,
        private readonly string $stripeWebhookSecret
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

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $product->getName(),
                        // 'images' => [$product->getMainImageUrl()],
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
            // Créer une session de paiement avec les métadonnées
            $checkoutSession = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'metadata' => [
                    'user_id' => $user->getId(),
                    // Vous pouvez également ajouter d'autres informations si nécessaire
                ],
                'success_url' => $this->generateUrl('payment_success', ['session_id' => '{CHECKOUT_SESSION_ID}'], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de l\'initialisation du paiement : ' . $e->getMessage());
            return $this->redirectToRoute('view_cart');
        }

        return $this->redirect($checkoutSession->url, 303);
    }

    #[Route('/payment-success', name: 'payment_success')]
    public function success(Request $request): Response
    {
        // Retirer temporairement l'annotation #[IsGranted('ROLE_USER')] si nécessaire
        // #[IsGranted('ROLE_USER')]

        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            $this->addFlash('error', 'Session de paiement invalide.');
            return $this->redirectToRoute('shop_index');
        }

        Stripe::setApiKey($this->stripeSecretKey);

        try {
            $session = StripeSession::retrieve($sessionId);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Impossible de récupérer la session de paiement : ' . $e->getMessage());
            return $this->redirectToRoute('shop_index');
        }

        // Récupérer les métadonnées
        $userId = $session->metadata->user_id;
        $user = $this->entityManager->getRepository(User::class)->find($userId);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('shop_index');
        }

        // Récupérer les line_items
        try {
            $lineItems = StripeSession::allLineItems($sessionId);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Impossible de récupérer les articles de la commande : ' . $e->getMessage());
            return $this->redirectToRoute('shop_index');
        }

        // Créer la commande
        $order = new Order();
        $order->setUser($user);
        $order->setOrderStatus(OrderStatus::COMPLETED);

        $total = 0;

        foreach ($lineItems->data as $item) {
            $productName = $item->description;
            $quantity = $item->quantity;
            $unitAmount = $item->price->unit_amount / 100;

            // Trouver le produit par son nom (vous pouvez ajuster en fonction de votre logique)
            $product = $this->entityManager->getRepository(Product::class)->findOneBy(['name' => $productName]);

            if (!$product) {
                continue;
            }

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
        // Vous devez configurer votre endpoint secret depuis le tableau de bord Stripe
        $endpointSecret = $this->stripeWebhookSecret;

        $payload = $request->getContent();
        $sigHeader = $request->headers->get('stripe-signature');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $endpointSecret
            );
        } catch (\UnexpectedValueException $e) {
            // Payload invalide
            return new Response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Signature invalide
            return new Response('Invalid signature', 400);
        }

        // Gérer l'événement
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            // Récupérer les métadonnées
            $userId = $session->metadata->user_id;
            $user = $this->entityManager->getRepository(User::class)->find($userId);

            if (!$user) {
                return new Response('User not found', 400);
            }

            // Récupérer les line_items
            try {
                $lineItems = StripeSession::allLineItems($session->id);
            } catch (\Exception $e) {
                return new Response('Failed to retrieve line items', 400);
            }

            // Créer la commande
            $order = new Order();
            $order->setUser($user);
            $order->setOrderStatus(OrderStatus::COMPLETED);

            $total = 0;

            foreach ($lineItems->data as $item) {
                $productName = $item->description;
                $quantity = $item->quantity;
                $unitAmount = $item->price->unit_amount / 100;

                // Trouver le produit par son nom (vous pouvez ajuster en fonction de votre logique)
                $product = $this->entityManager->getRepository(Product::class)->findOneBy(['name' => $productName]);

                if (!$product) {
                    continue;
                }

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
