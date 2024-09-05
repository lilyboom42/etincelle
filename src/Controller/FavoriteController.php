<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class FavoriteController extends AbstractController
{
    /**
     * Adds a product to the user's favorites.
     * Ajoute un produit aux favoris de l'utilisateur.
     */
    #[Route('/favorites/add/{productId}', name: 'add_favorite', methods: ['POST'])]
    public function addFavorite(int $productId, ProductRepository $productRepository, EntityManagerInterface $em, #[CurrentUser] $user): Response
    {
        // Find the product by its ID
        // Trouver le produit par son ID
        $product = $productRepository->find($productId);

        if (!$product) {
            // Throw an exception if the product does not exist
            // Lancer une exception si le produit n'existe pas
            throw $this->createNotFoundException('Le produit n\'existe pas.');
        }

        // Add the product to the user's favorites
        // Ajouter le produit aux favoris de l'utilisateur
        $user->addFavorite($product);
        $em->persist($user);
        $em->flush();

        // Redirect to the route that displays the favorites
        // Rediriger vers la route qui affiche les favoris
        return $this->redirectToRoute('some_route_to_display_favorites');
    }

    /**
     * Removes a product from the user's favorites.
     * Supprime un produit des favoris de l'utilisateur.
     */
    #[Route('/favorites/remove/{productId}', name: 'remove_favorite', methods: ['POST'])]
    public function removeFavorite(int $productId, ProductRepository $productRepository, EntityManagerInterface $em, #[CurrentUser] $user): Response
    {
        // Find the product by its ID
        // Trouver le produit par son ID
        $product = $productRepository->find($productId);

        if (!$product) {
            // Throw an exception if the product does not exist
            // Lancer une exception si le produit n'existe pas
            throw $this->createNotFoundException('Le produit n\'existe pas.');
        }

        // Remove the product from the user's favorites
        // Supprimer le produit des favoris de l'utilisateur
        $user->removeFavorite($product);
        $em->persist($user);
        $em->flush();

        // Redirect to the route that displays the favorites
        // Rediriger vers la route qui affiche les favoris
        return $this->redirectToRoute('some_route_to_display_favorites');
    }
}
