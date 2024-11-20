<?php

// src/Controller/RegistrationController.php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;


class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encode le mot de passe en clair
            $plainPassword = $form->get('plainPassword')->getData();
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

            // Envoyer un e-mail de bienvenue
            $email = (new Email())
                ->from('admin@sparkles.com')
                ->to($user->getEmail())
                ->subject('Bienvenue parmi nous')
                ->text('Bienvenue sur notre plateforme !')
                ->html($this->renderView('emails/registration.html.twig', ['user' => $user]));

            try {
                $mailer->send($email);
                $this->addFlash('success', 'Inscription réussie, un e-mail de bienvenue vous a été envoyé.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'L\'inscription a réussi, mais un problème est survenu lors de l\'envoi de l\'e-mail.');
            }

            // Rediriger vers la page de connexion après l'inscription
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}