<?php
// src/Service/OrderService.php

namespace App\Service;

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
        private readonly CartService $cartService
    ) {}

    /**
     * Crée une commande à partir du panier en session.
     *
     * @param User $user L'utilisateur qui passe la commande.
     * @param string $stripeSessionId L'ID de session Stripe associé à la commande.
     * @return Order La commande créée.
     * @throws InsufficientStockException Si le stock est insuffisant pour un produit.
     */
    public function createOrderFromSessionCart(User $user, string $stripeSessionId): Order
    {
        $cart = $this->cartService->getCart();

        if (empty($cart)) {
            throw new \Exception('Le panier est vide.');
        }

        $order = new Order();
        $order->setUser($user);
        $order->setOrderStatus(OrderStatus::COMPLETED);
        $order->setStripeSessionId($stripeSessionId);

        $total = 0;

        // Démarrer une transaction
        $this->entityManager->getConnection()->beginTransaction();

        try {
            foreach ($cart as $productId => $quantity) {
                $product = $this->entityManager->getRepository(Product::class)->find($productId);
                if (!$product) {
                    continue;
                }

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

                // Décrémenter le stock du produit
                $product->decrementStockQuantity($quantity);
                $this->entityManager->persist($product);
            }

            $order->setTotal($total);
            $this->entityManager->persist($order);
            $this->entityManager->flush();

            // Valider la transaction
            $this->entityManager->getConnection()->commit();

            // Vider le panier en session
            $this->cartService->clearCart();

            return $order;
        } catch (\Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->entityManager->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * Crée une commande à partir d'une session Stripe.
     *
     * @param StripeSession $session La session Stripe.
     * @return Order La commande créée.
     * @throws InvalidPaymentSessionException Si les données de session sont invalides.
     */
    public function createOrderFromStripeSession(StripeSession $session): Order
    {
        $userId = $session->metadata->user_id ?? null;

        if (!$userId) {
            throw new InvalidPaymentSessionException('Données de session invalides');
        }

        $user = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            throw new InvalidPaymentSessionException('Utilisateur non trouvé');
        }

        // Créer une commande à partir du panier en session
        $order = $this->createOrderFromSessionCart($user, $session->id);

        return $order;
    }

    /**
     * Vérifie si une commande a déjà été traitée à partir d'un session ID Stripe.
     *
     * @param string $sessionId L'ID de session Stripe.
     * @return bool Vrai si la commande existe déjà, sinon faux.
     */
    public function isOrderAlreadyProcessed(string $sessionId): bool
    {
        return null !== $this->entityManager->getRepository(Order::class)
            ->findOneBy(['stripeSessionId' => $sessionId]);
    }
}
