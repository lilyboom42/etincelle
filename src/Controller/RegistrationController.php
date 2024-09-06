<?php

// src/Controller/RegistrationController.php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

        // Dump pour vérifier l'état du formulaire juste après handleRequest
        dump($form->isSubmitted());

        // Vérifier que le formulaire est soumis avant de vérifier sa validité
        if ($form->isSubmitted()) {
            dump($form->isValid(), $form->getErrors(true, false));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer le mot de passe en clair
            $plainPassword = $form->get('plainPassword')->getData();

            // Dump pour vérifier la valeur du mot de passe
            dump($plainPassword);

            // Vérifiez que le mot de passe n'est pas vide après la soumission
            if (!$plainPassword) {
                $this->addFlash('error', 'Le mot de passe ne doit pas être vide.');
                return new RedirectResponse($this->generateUrl('app_register'));
            }

            // Encoder le mot de passe
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $plainPassword
                )
            );

            // Dump pour vérifier l'utilisateur avant la persistance
            dump($user);

            // Associe UserDetails avec l'utilisateur s'il existe
            $userDetails = $user->getUserDetail();
            if ($userDetails) {
                $userDetails->setUser($user); // Associe les détails à l'utilisateur
                $entityManager->persist($userDetails);
            }

            // Persister et enregistrer l'utilisateur
            $entityManager->persist($user);
            $entityManager->flush();

            // Rediriger vers la page de connexion après inscription réussie
            return $this->redirectToRoute('app_login');
        }

        // Afficher le formulaire d'inscription avec les erreurs si le formulaire est invalide
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}

