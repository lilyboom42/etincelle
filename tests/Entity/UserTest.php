<?php
// tests/Entity/UserTest.php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Cart;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetCart(): void
    {
        // Créer une instance de l'entité User
        $user = new User();

        // Créer une instance de l'entité Cart
        $cart = new Cart();

        // Associer le Cart à l'utilisateur
        $user->setCart($cart);

        // Vérifier que le Cart est bien associé à l'utilisateur
        $this->assertSame($cart, $user->getCart());

        // Vérifier que l'utilisateur est bien associé au Cart
        $this->assertSame($user, $cart->getUser());
    }

    public function testSetCart(): void
    {
        // Créer une instance de l'entité User
        $user = new User();

        // Créer une instance de l'entité Cart
        $cart = new Cart();

        // Associer le Cart à l'utilisateur
        $user->setCart($cart);

        // Vérifier que le Cart est bien associé à l'utilisateur
        $this->assertSame($cart, $user->getCart());
    }

    public function testSetCartNull(): void
    {
        // Créer une instance de l'entité User
        $user = new User();

        // Créer une instance de l'entité Cart
        $cart = new Cart();

        // Associer le Cart à l'utilisateur
        $user->setCart($cart);

        // Désassocier le Cart de l'utilisateur
        $user->setCart(null); 

        // Vérifier que le Cart est maintenant null
        $this->assertNull($user->getCart());

        // Vérifier que le Cart n'a plus d'utilisateur
        $this->assertNull($cart->getUser());
    }
}
