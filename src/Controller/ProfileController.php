<?php

namespace App\Controller;

use App\Entity\UserDetails; // Assurez-vous d'importer correctement la classe
use App\Entity\User;
use App\Form\UserProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'profile_index')]
    public function index(Request $request, EntityManagerInterface $entityManager, UserInterface $user, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Vérifier que l'utilisateur est bien une instance de la classe User
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Accès non autorisé.');
        }

        // Récupérer ou initialiser les détails utilisateur
        $userDetails = $user->getUserDetail();
        if (!$userDetails) {
            $userDetails = new UserDetails(); // S'assurer que UserDetails est correctement importé
            $user->setUserDetail($userDetails);
        }

        // Créer le formulaire de profil utilisateur
        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mise à jour du mot de passe si nécessaire
            $plainPassword = $user->getPlainPassword();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
        }

        // Récupérer les commandes de l'utilisateur
        $orders = $user->getOrders();

        return $this->render('profile/index.html.twig', [
            'form' => $form->createView(),
            'orders' => $orders, // Passer les commandes à la vue
        ]);
    }
}
