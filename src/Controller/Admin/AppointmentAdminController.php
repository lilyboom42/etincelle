<?php

namespace App\Controller\Admin;

use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/appointment')]
class AppointmentAdminController extends AbstractController
{
    #[Route('/', name: 'admin_appointment_index')]
    public function index(AppointmentRepository $appointmentRepository): Response
    {
        $appointments = $appointmentRepository->findBy(['status' => 'pending']);

        return $this->render('admin/appointment/index.html.twig', [
          'appointments' => $appointments,
        ]);
    }

    #[Route('/approve/{id}', name: 'admin_appointment_approve')]
    public function approve(Appointment $appointment, EntityManagerInterface $entityManager): Response
    {
        $appointment->setStatus('approved');
        $entityManager->flush();

        // Envoyer une notification au client pour le paiement

        $this->addFlash('success', 'Le rendez-vous a été approuvé.');

        return $this->redirectToRoute('admin_appointment_index');
    }

    #[Route('/reject/{id}', name: 'admin_appointment_reject')]
    public function reject(Appointment $appointment, EntityManagerInterface $entityManager): Response
    {
        $appointment->setStatus('rejected');
        $entityManager->flush();

        // Envoyer une notification au client pour le refus

        $this->addFlash('info', 'Le rendez-vous a été rejeté.');

        return $this->redirectToRoute('admin_appointment_index');
    }
}
