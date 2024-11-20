<?php
// src/Controller/OrderController.php

namespace App\Controller;

use App\Entity\Order;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/validate-order', name: 'validate_order', methods: ['POST'])]
    public function validateOrder(Order $order): Response
    {
        // Logique de validation de la commande si nécessaire

        $this->addFlash('success', 'Commande validée avec succès.');

        return $this->redirectToRoute('order_success_page');
    }

    #[Route('/order-success', name: 'order_success_page', methods: ['GET'])]
    public function orderSuccess(): Response
    {
        return $this->render('order/success.html.twig');
    }
}
