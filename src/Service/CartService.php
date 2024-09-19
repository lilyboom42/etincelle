<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Cart;
use App\Entity\Product;
use App\Entity\CartItem;
use Doctrine\ORM\EntityManagerInterface;

class CartService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Récupérer ou créer un panier pour l'utilisateur.
     */
    public function getOrCreateCart(User $user): Cart
    {
        // Utilisation de la méthode getCart de l'entité User
        $cart = $user->getCart();
        
        if (!$cart) {
            // Si l'utilisateur n'a pas encore de panier, en créer un
            $cart = new Cart();
            $cart->setUser($user);
            $this->entityManager->persist($cart);
            $this->entityManager->flush();
        }
        
        return $cart;
    }

    /**
     * Ajouter ou mettre à jour un article dans le panier.
     */
    public function addOrUpdateCartItem(Cart $cart, Product $product, int $quantity): void
    {
        // Récupérer l'article du panier s'il existe déjà
        $cartItem = $this->entityManager->getRepository(CartItem::class)
            ->findOneBy(['cart' => $cart, 'product' => $product]);

        if ($cartItem) {
            // Mettre à jour la quantité
            $cartItem->setQuantity($quantity);
        } else {
            // Si l'article n'existe pas, le créer
            $cartItem = new CartItem();
            $cartItem->setCart($cart);
            $cartItem->setProduct($product);
            $cartItem->setQuantity($quantity);
            $this->entityManager->persist($cartItem);
        }

        // Sauvegarder les changements dans la base de données
        $this->entityManager->flush();
    }

    /**
     * Supprimer un article du panier.
     */
    public function removeItemFromCart(Cart $cart, Product $product): void
    {
        // Récupérer l'article du panier
        $cartItem = $this->entityManager->getRepository(CartItem::class)
            ->findOneBy(['cart' => $cart, 'product' => $product]);

        if ($cartItem) {
            // Supprimer l'article et sauvegarder
            $this->entityManager->remove($cartItem);
            $this->entityManager->flush();
        }
    }
}
