<?php

namespace App\Controller;

use App\Entity\Order;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    #[Route('/validate-order', name: 'validate_order')]
    public function validateOrder(Order $order): Response
    {
        
    $this->addFlash('success', 'Commande validée avec succès.');

    return $this->redirectToRoute('order_success_page');
    }
}
