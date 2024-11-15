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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail; // Ajout de cet import
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/orders')]
#[IsGranted('ROLE_ADMIN')]
class AdminOrderController extends AbstractController
{
    #[Route('/', name: 'admin_order_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $orders = $entityManager->getRepository(Order::class)->findAll();
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
    public function addTracking(Order $order, Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        // Vérification du token CSRF
        $csrfToken = new CsrfToken('add-tracking', $request->request->get('_token'));
        if (!$csrfTokenManager->isTokenValid($csrfToken)) {
            $this->addFlash('error', 'Action non autorisée.');
            return $this->redirectToRoute('admin_order_index');
        }

        $trackingNumber = $request->request->get('tracking_number');
    
        // Vérifie si le numéro de suivi est bien transmis
        if (!$trackingNumber) {
            $this->addFlash('error', 'Veuillez entrer un numéro de suivi.');
            return $this->redirectToRoute('admin_order_index');
        }
    
        // Met à jour le numéro de suivi et le statut
        $order->setTrackingNumber($trackingNumber);
        $order->setOrderStatus(OrderStatus::SHIPPED);
        $entityManager->flush();
    
        // Envoi de l’e-mail en utilisant le template Twig
        $email = (new TemplatedEmail())
            ->from('admin@example.com')
            ->to($order->getUser()->getEmail())
            ->bcc('admin@example.com')
            ->subject('Votre commande a été expédiée')
            ->htmlTemplate('emails/order_shipped.html.twig')
            ->context([
                'order' => $order,
                'trackingNumber' => $trackingNumber
            ]);
    
        try {
            $mailer->send($email);
            $this->addFlash('success', 'Numéro de suivi ajouté et e-mail envoyé');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Échec de l\'envoi de l\'e-mail : ' . $e->getMessage());
        }
    
        return $this->redirectToRoute('admin_order_index');
    }
}
