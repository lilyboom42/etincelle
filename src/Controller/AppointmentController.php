<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Service;
use App\Entity\Status; // Import de l'entité Status
use App\Form\AppointmentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppointmentController extends AbstractController
{
    #[Route('/appointment/new', name: 'appointment_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupération des services pour afficher la page même pour les visiteurs
        $services = $entityManager->getRepository(Service::class)->findAll();

        // Vérifier si l'utilisateur est connecté
        $user = $this->getUser();

        // Initialiser la variable du formulaire à null pour les visiteurs non connectés
        $form = null;

        if ($user) {
            $appointment = new Appointment();
            $appointment->setUser($user);

            // Récupérer le statut 'en attente' depuis la base de données
            $pendingStatus = $entityManager->getRepository(Status::class)->findOneBy(['name' => 'en attente']);
            if (!$pendingStatus) {
                throw $this->createNotFoundException("Statut 'en attente' introuvable.");
            }
            $appointment->setStatus($pendingStatus);

            // Créer le formulaire pour les utilisateurs connectés
            $form = $this->createForm(AppointmentType::class, $appointment);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($appointment);
                $entityManager->flush();

                $this->addFlash('success', 'Votre rendez-vous a été demandé avec succès.');
                return $this->redirectToRoute('appointment_new');
            }
        } else {
            // Message flash pour indiquer que l'utilisateur doit se connecter
            $this->addFlash('info', 'Connectez-vous pour prendre un rendez-vous.');
        }

        return $this->render('appointment/new.html.twig', [
            'form' => $form ? $form->createView() : null, // Formulaire si connecté, null sinon
            'services' => $services,
        ]);
    }
}
