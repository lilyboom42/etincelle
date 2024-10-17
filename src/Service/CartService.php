<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Cart;
use App\Entity\Product;
use App\Entity\CartItem;
use Doctrine\ORM\EntityManagerInterface;

class CartService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function getOrCreateCart(User $user): Cart
    {
        $cart = $user->getCart();
        
        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $this->entityManager->persist($cart);
            $this->entityManager->flush();
        }
        
        return $cart;
    }

    public function addToCart(Cart $cart, $product, int $quantity = 1): void
    {
        // Chercher si le produit existe déjà dans le panier
        $cartItem = null;
        foreach ($cart->getCartItems() as $item) {
            if ($item->getProduct() === $product) {
                $cartItem = $item;
                break;
            }
        }

        if ($cartItem) {
            // Mettre à jour la quantité si le produit existe déjà
            $cartItem->setQuantity($cartItem->getQuantity() + $quantity);
        } else {
            // Créer un nouveau CartItem si le produit n'existe pas
            $cartItem = new CartItem();
            $cartItem->setCart($cart);
            $cartItem->setProduct($product);
            $cartItem->setQuantity($quantity);
            $cart->addCartItem($cartItem);
        }

        $this->entityManager->persist($cartItem);
        $this->entityManager->flush();
    }
}
