<?php

namespace App\Controller;

use App\Entity\Appointment;
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
        $appointment = new Appointment();
        $form = $this->createForm(AppointmentType::class, $appointment);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $appointment->setUser($this->getUser());
            $appointment->setStatus('pending');

            $entityManager->persist($appointment);
            $entityManager->flush();

            $this->addFlash('success', 'Votre demande de rendez-vous a été soumise.');

            return $this->redirectToRoute('home');
        }

        return $this->render('appointment/new.html.twig', [
          'form' => $form->createView(),
        ]);
    }
}
