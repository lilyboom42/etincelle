<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    private ProductRepository $productRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(ProductRepository $productRepository, EntityManagerInterface $entityManager)
    {
        $this->productRepository = $productRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/products/available', name: 'products_available')]
    public function availableProducts(): Response
    {
        $products = $this->productRepository->findAvailableProducts();
        return $this->render('product/available.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/product/new', name: 'product_new')]
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($product);
            $this->entityManager->flush();

            return $this->redirectToRoute('products_available');
        }

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
