<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserDetails;
use App\Form\UserProfileType;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'profile_index')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        UserInterface $user,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer,
        AppointmentRepository $appointmentRepository
    ): Response {
        // Vérifier que l'utilisateur est bien une instance de la classe User
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Accès non autorisé.');
        }

        // Gestion du formulaire de profil
        $userDetails = $user->getUserDetail();

        if (!$userDetails) {
            $userDetails = new UserDetails();
            $user->setUserDetail($userDetails);
        }

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

            // Envoi d'un e-mail après modification du profil
            $email = (new Email())
                ->from('no-reply@votre-site.com') // Remplacez par votre adresse e-mail d'envoi
                ->to($user->getEmail())
                ->subject('Modification de votre profil')
                ->html($this->renderView('emails/change.html.twig'));

            $mailer->send($email);
        }

        // Récupérer les commandes de l'utilisateur
        $orders = $user->getOrders();

        // Récupérer les rendez-vous de l'utilisateur
        $appointments = $appointmentRepository->findBy(['user' => $user]);

        return $this->render('profile/index.html.twig', [
            'form' => $form->createView(),
            'orders' => $orders,
            'appointments' => $appointments,
        ]);
    }
}
