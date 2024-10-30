<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ResetPasswordRequestFormType;
use App\Form\ResetPasswordFormType;

class ResetPasswordController extends AbstractController
{
    private $entityManager;
    private $mailer;

    public function __construct(EntityManagerInterface $entityManager, MailerInterface $mailer)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
    }

    // Demande de réinitialisation du mot de passe
    #[Route('/reset-password', name: 'app_forgotten_password')]
    public function request(Request $request, TokenGeneratorInterface $tokenGenerator)
    {
        // Création du formulaire pour saisir l'email
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $emailData = $form->getData();
            $email = $emailData['email'];

            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('danger', 'Email non trouvé');
                return $this->redirectToRoute('app_forgotten_password');
            }

            // Générer un jeton de réinitialisation
            $token = $tokenGenerator->generateToken();
            $user->setResetToken($token);
            $this->entityManager->flush();

            // Générer l'URL de réinitialisation
            $url = $this->generateUrl('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

            // Envoyer l'email avec le lien de réinitialisation
            $emailMessage = (new Email())
                ->from('admin@etincele.com')
                ->to($user->getEmail())
                ->subject('Demande de réinitialisation de mot de passe')
                ->html($this->renderView('emails/reset_password.html.twig', ['url' => $url]));

            $this->mailer->send($emailMessage);

            $this->addFlash('success', 'Un email a été envoyé avec un lien pour réinitialiser votre mot de passe');
            return $this->redirectToRoute('app_forgotten_password');
        }

        return $this->render('security/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Réinitialisation du mot de passe via le lien reçu par email
    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function reset(Request $request, string $token, UserPasswordHasherInterface $passwordHasher)
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['resetToken' => $token]);

        if (!$user) {
            $this->addFlash('danger', 'Jeton invalide');
            return $this->redirectToRoute('app_forgotten_password');
        }

        // Créer le formulaire pour soumettre le nouveau mot de passe
        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('password')->getData();

            // Encodage et mise à jour du mot de passe
            $encodedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($encodedPassword);

            // Invalidation du token après réinitialisation
            $user->setResetToken(null);
            $this->entityManager->flush();

            $this->addFlash('success', 'Mot de passe réinitialisé avec succès');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset.html.twig', [
            'form' => $form->createView(),
            'token' => $token,
        ]);
    }
}
