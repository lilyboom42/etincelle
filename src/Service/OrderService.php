<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\OrderLine;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\OrderStatus;
use App\Exception\InsufficientStockException;
use App\Exception\InvalidPaymentSessionException;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session as StripeSession;

class OrderService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function createOrderFromCart(Cart $cart): Order
    {
        $order = new Order();
        $order->setUser($cart->getUser());
        $order->setOrderStatus(OrderStatus::COMPLETED); // Utiliser l'énumération
        $order->setTotal($cart->getTotal());
        // La date de création sera automatiquement définie si vous avez un lifecycle callback

        $this->entityManager->persist($order);
        $cart->clearItems();
        $this->entityManager->flush();

        return $order;
    }
    public function isOrderAlreadyProcessed(string $sessionId): bool
    {
        return null !== $this->entityManager->getRepository(Order::class)
            ->findOneBy(['stripeSessionId' => $sessionId]);
    }

    public function createOrderFromStripeSession(StripeSession $session): Order
    {
        $userId = $session->metadata->user_id;
        $cart = json_decode($session->metadata->cart, true);

        if (!$userId || !$cart) {
            throw new InvalidPaymentSessionException('Données de session invalides');
        }

        $user = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            throw new InvalidPaymentSessionException('Utilisateur non trouvé');
        }

        $order = new Order();
        $order->setUser($user);
        $order->setOrderStatus(OrderStatus::COMPLETED);
        $order->setStripeSessionId($session->id);

        $total = $this->processOrderLines($order, $cart);
        $order->setTotal($total);

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $order;
    }

    private function processOrderLines(Order $order, array $cart): float
    {
        $total = 0;

        foreach ($cart as $productId => $details) {
            $product = $this->entityManager->getRepository(Product::class)->find($productId);
            if (!$product) {
                continue;
            }

            $quantity = $details['quantity'];
            if ($product->getStockQuantity() < $quantity) {
                throw new InsufficientStockException(
                    sprintf('Stock insuffisant pour le produit "%s"', $product->getName())
                );
            }

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

        return $total;
    }
}
