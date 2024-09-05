<?php

namespace App\Controller;

use App\Repository\UserDetailsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private UserDetailsRepository $userDetailsRepository;

    /**
     * Constructor to inject UserDetailsRepository.
     * Constructeur pour injecter le UserDetailsRepository.
     */
    public function __construct(UserDetailsRepository $userDetailsRepository)
    {
        $this->userDetailsRepository = $userDetailsRepository;
    }

    /**
     * Displays users filtered by country.
     * Affiche les utilisateurs filtrés par pays.
     */
    #[Route('/users/by-country/{country}', name: 'users_by_country')]
    public function usersByCountry(string $country): Response
    {
        // Retrieve users based on the specified country.
        // Récupère les utilisateurs en fonction du pays spécifié.
        $users = $this->userDetailsRepository->findByCountry($country);

        // Use the retrieved data to build the response.
        // Utilisez les données récupérées pour construire la réponse.
        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }
}
