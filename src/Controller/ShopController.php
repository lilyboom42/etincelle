<?php

// src/Controller/ShopController.php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShopController extends AbstractController
{
    #[Route('/boutique', name: 'shop_index')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        return $this->render('product/boutique.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/ajouter-au-panier/{id}', name: 'add_to_cart')]
    public function addToCart(Product $product): RedirectResponse
    {
        // Logique d'ajout au panier
        // Exemple de stockage dans la session ou base de données

        // Message flash pour confirmer l'ajout
        $this->addFlash('success', 'Produit ajouté au panier !');

        // Redirection vers la boutique
        return $this->redirectToRoute('shop_index');
    }
}
