<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Service;
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
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour prendre un rendez-vous.');
            return $this->redirectToRoute('app_login'); 
        }

        $appointment = new Appointment();
        $appointment->setUser($user);

        $services = $entityManager->getRepository(Service::class)->findAll();
        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($appointment);
            $entityManager->flush();

            $this->addFlash('success', 'Votre rendez-vous a été demandé avec succès.');
            return $this->redirectToRoute('appointment_new');
        }

        return $this->render('appointment/new.html.twig', [
            'form' => $form->createView(),
            'services' => $services,
        ]);
    }
}
