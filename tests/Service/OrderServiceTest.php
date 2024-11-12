<?php
// tests/Service/OrderServiceTest.php

namespace App\Tests\Service;

use App\Service\OrderService;
use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class OrderServiceTest extends TestCase
{
    /**
     * @var EntityManagerInterface&MockObject
     */
    private $entityManager;

    /**
     * @var Cart&MockObject
     */
    private $cart;

    private OrderService $orderService;

    protected function setUp(): void
    {
        // Créer un mock de EntityManagerInterface
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        // Créer un mock de Cart
        $this->cart = $this->createMock(Cart::class);

        // Passer le mock à l'instance de OrderService
        $this->orderService = new OrderService($this->entityManager);
    }

    public function testCreateOrderFromCart(): void
    {
        // Créer un mock pour User
        $user = $this->createMock(User::class);

        // Simuler le comportement attendu de Cart
        $this->cart->expects($this->once())
            ->method('getUser')
            ->willReturn($user);  // Retourner un objet User simulé

        $this->cart->expects($this->once())
            ->method('getTotal')
            ->willReturn(100.0);  // Retourner un float pour le total

        // Simuler la méthode persist de l'EntityManager
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Order::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        // Tester la méthode createOrderFromCart
        $order = $this->orderService->createOrderFromCart($this->cart);

        // Vérification
        $this->assertInstanceOf(Order::class, $order);  // Vérifier que c'est un objet Order
        $this->assertEquals(100.0, $order->getTotal());  // Vérifier que le total de la commande est correct
    }
}
