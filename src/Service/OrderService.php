<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\Order;
use App\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

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
}
