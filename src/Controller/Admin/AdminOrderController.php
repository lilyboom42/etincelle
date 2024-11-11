<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Enum\OrderStatus;

#[Route('/admin/orders')]
class AdminOrderController extends AbstractController
{
    #[Route('/', name: 'admin_order_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $orders = $entityManager->getRepository(Order::class)->findBy(['orderStatus' => OrderStatus::PAID]); // Utilisez 'orderStatus' au lieu de 'status'
        $products = $entityManager->getRepository(Product::class)->findAll();

        return $this->render('admin/orders/index.html.twig', [
            'orders' => $orders,
            'products' => $products,
        ]);
    }
    #[Route('/update-stock/{id}', name: 'admin_product_update_stock', methods: ['POST'])]
    public function updateStock(Product $product, Request $request, EntityManagerInterface $entityManager): Response
    {
        $newStock = $request->request->get('stock');
        $product->setStockQuantity($newStock);
        $entityManager->flush();

        $this->addFlash('success', 'Stock mis à jour');
        return $this->redirectToRoute('admin_order_index');
    }

    #[Route('/add-tracking/{id}', name: 'admin_order_add_tracking', methods: ['POST'])]
    public function addTracking(Order $order, Request $request, EntityManagerInterface $entityManager): Response
    {
        $trackingNumber = $request->request->get('tracking_number');
        $order->setTrackingNumber($trackingNumber);
        $order->setOrderStatus(OrderStatus::SHIPPED); // Utilisez 'orderStatus' ici
        $entityManager->flush();

        $this->addFlash('success', 'Numéro de suivi ajouté');
        return $this->redirectToRoute('admin_order_index');
    }
}
