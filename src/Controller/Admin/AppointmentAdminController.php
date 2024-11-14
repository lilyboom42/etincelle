<?php 

namespace App\Controller\Admin;

use App\Entity\Appointment;
use App\Entity\Status; // Import de l'entité Status
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request; // Import de Request
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/appointment')]
#[IsGranted('ROLE_ADMIN')]
class AppointmentAdminController extends AbstractController
{
    #[Route('/pending', name: 'admin_appointment_pending', methods: ['GET'])]
    public function pending(EntityManagerInterface $entityManager): Response
    {
        // Récupérer le statut 'demandé'
        $requestedStatus = $entityManager->getRepository(Status::class)->findOneBy(['name' => 'demandé']);
        if (!$requestedStatus) {
            throw $this->createNotFoundException("Le statut 'demandé' n'a pas été trouvé dans la base de données.");
        }
    
        $appointments = $entityManager->getRepository(Appointment::class)
            ->findBy(['status' => $requestedStatus]);
    
        return $this->render('admin/appointment/pending.html.twig', [
            'appointments' => $appointments,
        ]);
    }

    #[Route('/approve/{id}', name: 'admin_appointment_approve', methods: ['POST'])]
    public function approve(Request $request, Appointment $appointment, EntityManagerInterface $entityManager): Response
    {
        // Validation du token CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('approve' . $appointment->getId(), $token)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_appointment_pending');
        }
    
        // Récupérer le statut 'approuvé'
        $approvedStatus = $entityManager->getRepository(Status::class)->findOneBy(['name' => 'approuvé']);
        if (!$approvedStatus) {
            $this->addFlash('error', "Le statut 'approuvé' n'a pas été trouvé.");
            return $this->redirectToRoute('admin_appointment_pending');
        }
    
        // Mise à jour du statut en 'approuvé'
        $appointment->setStatus($approvedStatus);
        $entityManager->flush();
    
        $this->addFlash('success', 'Rendez-vous approuvé avec succès.');
        return $this->redirectToRoute('admin_appointment_pending');
    }

    #[Route('/reject/{id}', name: 'admin_appointment_reject', methods: ['POST'])]
    public function reject(Request $request, Appointment $appointment, EntityManagerInterface $entityManager): Response
    {
        // Validation du token CSRF
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('reject' . $appointment->getId(), $token)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_appointment_pending');
        }

        // Récupérer le statut 'rejeté'
        $rejectedStatus = $entityManager->getRepository(Status::class)->findOneBy(['name' => 'rejeté']);
        if (!$rejectedStatus) {
            $this->addFlash('error', "Le statut 'rejeté' n'a pas été trouvé.");
            return $this->redirectToRoute('admin_appointment_pending');
        }

        // Mise à jour du statut en 'rejeté'
        $appointment->setStatus($rejectedStatus);
        $entityManager->flush();

        $this->addFlash('success', 'Rendez-vous rejeté avec succès.');
        return $this->redirectToRoute('admin_appointment_pending');
    }

    #[Route('/paid', name: 'paid_appointments', methods: ['GET'])]
    public function paidAppointments(EntityManagerInterface $entityManager): Response
    {
        // Récupérer le statut 'payé'
        $paidStatus = $entityManager->getRepository(Status::class)->findOneBy(['name' => 'payé']);
        if (!$paidStatus) {
            throw $this->createNotFoundException("Le statut 'payé' n'a pas été trouvé dans la base de données.");
        }

        $paidAppointments = $entityManager->getRepository(Appointment::class)
            ->findBy(['status' => $paidStatus]);

        return $this->render('admin/appointment/paid.html.twig', [
            'appointments' => $paidAppointments,
        ]);
    }
}
