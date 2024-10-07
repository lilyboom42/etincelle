<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // /** @var string $plainPassword */
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
            $email = (new Email())
            ->from('admin@etincele.com')
            ->to($user->getEmail())
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('bienvenu parmis nous')
            ->text('Sending emails is fun again!')
            // ->html('<p>See Twig integration for better HTML integration!</p>');
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
