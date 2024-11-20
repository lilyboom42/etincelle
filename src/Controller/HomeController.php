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

    #[Route('/confidentiality', name: 'confidentiality')]
    public function confidentiality(): Response
    {
        return $this->render('others/confidentiality.html.twig');
    }
    
    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        return $this->render('others/contact.html.twig');
    }

    #[Route('/terms-of-use', name: 'terms_of_use')]
    public function termsOfUse(): Response
    {
        return $this->render('others/terms_of_use.html.twig');
    }

}
