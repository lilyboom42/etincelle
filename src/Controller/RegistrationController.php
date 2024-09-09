<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Encode le mot de passe en clair
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // Associe UserDetails avec l'utilisateur
            $userDetails = $user->getUserDetail();
            if ($userDetails) {
                $userDetails->setUser($user); // Lien bidirectionnel
                $entityManager->persist($userDetails);
            }

            // Enregistre l'utilisateur
            $entityManager->persist($user);
            $entityManager->flush();

            // Faites tout autre traitement nécessaire, comme envoyer un e-mail

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
