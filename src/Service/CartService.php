<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private const CART_SESSION_KEY = 'cart';

    public function __construct(
        private readonly RequestStack $requestStack
    ) {}

    public function getCart(): array
    {
        return $this->requestStack->getSession()->get(self::CART_SESSION_KEY, []);
    }

    public function saveCart(array $cart): void
    {
        $this->requestStack->getSession()->set(self::CART_SESSION_KEY, $cart);
    }

    public function addToCart(int $productId, int $quantity = 1): void
    {
        $cart = $this->getCart();
        if (isset($cart[$productId])) {
            $cart[$productId] += $quantity;
        } else {
            $cart[$productId] = $quantity;
        }
        $this->saveCart($cart);
    }

    public function removeFromCart(int $productId): void
    {
        $cart = $this->getCart();
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            $this->saveCart($cart);
        }
    }

    public function updateCartItemQuantity(int $productId, int $newQuantity): void
    {
        $cart = $this->getCart();
        if ($newQuantity > 0) {
            $cart[$productId] = $newQuantity;
        } else {
            unset($cart[$productId]);
        }
        $this->saveCart($cart);
    }

    public function clearCart(): void
    {
        $this->saveCart([]);
    }
}
