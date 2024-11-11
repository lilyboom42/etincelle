<?php

namespace App\Controller\Admin;

use App\Entity\Appointment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Enum\AppointmentStatus;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/appointment')]
#[IsGranted('ROLE_ADMIN')]
class AppointmentAdminController extends AbstractController
{
    #[Route('/pending', name: 'admin_appointment_pending', methods: ['GET'])]
    public function pending(EntityManagerInterface $entityManager): Response
    {
        $appointments = $entityManager->getRepository(Appointment::class)
            ->findBy(['status' => AppointmentStatus::PENDING]);

        return $this->render('admin/appointment/pending.html.twig', [
            'appointments' => $appointments,
        ]);
    }

    #[Route('/approve/{id}', name: 'admin_appointment_approve', methods: ['POST'])]
    public function approve(Appointment $appointment, EntityManagerInterface $entityManager): Response
    {
        $appointment->setStatus(AppointmentStatus::APPROVED);
        $entityManager->flush();

        $this->addFlash('success', 'Rendez-vous approuvé avec succès.');
        return $this->redirectToRoute('admin_appointment_pending');
    }

    #[Route('/reject/{id}', name: 'admin_appointment_reject', methods: ['POST'])]
    public function reject(Appointment $appointment, EntityManagerInterface $entityManager): Response
    {
        $appointment->setStatus(AppointmentStatus::REJECTED);
        $entityManager->flush();

        $this->addFlash('success', 'Rendez-vous rejeté avec succès.');
        return $this->redirectToRoute('admin_appointment_pending');
    }

    #[Route('/paid', name: 'paid_appointments', methods: ['GET'])]
    public function paidAppointments(EntityManagerInterface $entityManager): Response
    {
        $paidAppointments = $entityManager->getRepository(Appointment::class)
            ->findBy(['status' => AppointmentStatus::APPROVED]);

        return $this->render('admin/appointment/paid.html.twig', [
            'appointments' => $paidAppointments,
        ]);
    }
}
