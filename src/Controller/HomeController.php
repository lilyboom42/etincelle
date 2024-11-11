<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ProductRepository $productRepository): Response
    {
        // Récupérer les 6 derniers produits ajoutés, triés par date de création décroissante
        $latestProducts = $productRepository->findBy([], ['createdAt' => 'DESC'], 6);

        return $this->render('home.html.twig', [
            'latestProducts' => $latestProducts,
        ]);
    }
}
