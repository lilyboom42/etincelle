<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/favorite')]
#[IsGranted('ROLE_USER')]
class FavoriteController extends AbstractController
{
    #[Route('/add/{id}', name: 'favorite_add', methods: ['POST'])]
    public function add(Product $product, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof \App\Entity\User) {
            throw new \LogicException('L\'utilisateur n\'est pas authentifié ou n\'est pas une instance de User.');
        }

        if (!$user->getFavorites()->contains($product)) {
            $user->addFavorite($product);
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Produit ajouté aux favoris.');
        } else {
            $this->addFlash('info', 'Ce produit est déjà dans vos favoris.');
        }

        // Redirection vers la page précédente
        $referer = $request->headers->get('referer');
        return $this->redirect($referer ? $referer : $this->generateUrl('shop_index'));
    }

    #[Route('/remove/{id}', name: 'favorite_remove', methods: ['POST'])]
    public function remove(Product $product, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof \App\Entity\User) {
            throw new \LogicException('L\'utilisateur n\'est pas authentifié ou n\'est pas une instance de User.');
        }

        if ($user->getFavorites()->contains($product)) {
            $user->removeFavorite($product);
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Produit retiré des favoris.');
        } else {
            $this->addFlash('info', 'Ce produit n\'est pas dans vos favoris.');
        }

        // Redirection vers la page précédente
        $referer = $request->headers->get('referer');
        return $this->redirect($referer ? $referer : $this->generateUrl('shop_index'));
    }

    #[Route('/', name: 'favorite_list', methods: ['GET'])]
    public function list(): Response
    {
        $user = $this->getUser();

        if (!$user instanceof \App\Entity\User) {
            throw new \LogicException('L\'utilisateur n\'est pas authentifié ou n\'est pas une instance de User.');
        }

        $favorites = $user->getFavorites();

        return $this->render('favorite/list.html.twig', [
            'favorites' => $favorites,
        ]);
    }

    // Méthode pour ajouter un favori en AJAX
    #[Route('/add-ajax/{id}', name: 'favorite_add_ajax', methods: ['POST'])]
    public function addAjax(Product $product, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof \App\Entity\User) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié.'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$user->getFavorites()->contains($product)) {
            $user->addFavorite($product);
            $entityManager->persist($user);
            $entityManager->flush();

            return new JsonResponse(['message' => 'Produit ajouté aux favoris.']);
        }

        return new JsonResponse(['message' => 'Produit déjà dans vos favoris.']);
    }

    // Méthode pour supprimer un favori en AJAX
    #[Route('/remove-ajax/{id}', name: 'favorite_remove_ajax', methods: ['POST'])]
    public function removeAjax(Product $product, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof \App\Entity\User) {
            return new JsonResponse(['error' => 'Utilisateur non authentifié.'], Response::HTTP_UNAUTHORIZED);
        }

        if ($user->getFavorites()->contains($product)) {
            $user->removeFavorite($product);
            $entityManager->persist($user);
            $entityManager->flush();

            return new JsonResponse(['message' => 'Produit retiré des favoris.']);
        }

        return new JsonResponse(['message' => 'Produit non trouvé dans vos favoris.']);
    }
}
