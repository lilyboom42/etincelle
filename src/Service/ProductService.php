<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductService
{
    private EntityManagerInterface $entityManager;
    private SessionInterface $session;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack, UrlGeneratorInterface $urlGenerator)
    {
        $this->entityManager = $entityManager;
        // Utilisation de RequestStack pour récupérer la session
        $this->session = $requestStack->getSession();
        $this->urlGenerator = $urlGenerator;
    }

    public function decreaseStock(Product $product, int $quantity): void
    {
        try {
            $product->decreaseStock($quantity);
            $this->entityManager->persist($product);
        } catch (\Exception $e) {
            // Accès direct au FlashBag via la session
            $flashBag = $this->session->getBag('flashes');
            if ($flashBag instanceof \Symfony\Component\HttpFoundation\Session\Flash\FlashBag) {
                $flashBag->add('error', $e->getMessage());
            }
            throw $e;
        }
    }

    public function validateOrder(Order $order): string
    {
        foreach ($order->getOrderLines() as $orderLine) {
            $product = $orderLine->getProduct();
            $quantity = $orderLine->getQuantity();

            try {
                $this->decreaseStock($product, $quantity);
            } catch (\Exception $e) {
                return $this->urlGenerator->generate('view_cart');
            }
        }

        $this->entityManager->flush();

        // Ajout d'un message flash de succès via la session
        $flashBag = $this->session->getBag('flashes');
        if ($flashBag instanceof \Symfony\Component\HttpFoundation\Session\Flash\FlashBag) {
            $flashBag->add('success', 'Commande validée avec succès.');
        }

        return $this->urlGenerator->generate('order_success');
    }
}
