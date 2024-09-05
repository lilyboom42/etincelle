<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    private ProductRepository $productRepository;

    /**
     * Constructor to inject the ProductRepository dependency.
     * Constructeur pour injecter la dépendance ProductRepository.
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Display available products.
     * Affiche les produits disponibles.
     */
    #[Route('/products/available', name: 'products_available')]
    public function availableProducts(): Response
    {
        // Retrieve available products from the repository.
        // Récupérer les produits disponibles depuis le repository.
        $products = $this->productRepository->findAvailableProducts();

        // Render the view with the available products.
        // Affiche la vue avec les produits disponibles.
        return $this->render('product/available.html.twig', [
            'products' => $products,
        ]);
    }
}
