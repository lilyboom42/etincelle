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

    #[Route('/product/new', name: 'product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Persister chaque image
            foreach ($product->getProductImages() as $productImage) {
                $productImage->setProduct($product);
                $entityManager->persist($productImage);
            }
    
            $entityManager->persist($product);
            $entityManager->flush();
    
            $this->addFlash('success', 'Produit ajouté avec succès.');
    
            return $this->redirectToRoute('shop_index');
        }
    
        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
}
