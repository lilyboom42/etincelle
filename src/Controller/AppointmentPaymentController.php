<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Payment;
use App\Entity\Status; // Import de l'entité Status
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\CalendarLinkService;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/appointment/payment')]
class AppointmentPaymentController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private StripeService $stripeService;
    private MailerInterface $mailer;
    private CalendarLinkService $calendarLinkService;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        StripeService $stripeService,
        MailerInterface $mailer,
        CalendarLinkService $calendarLinkService
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->stripeService = $stripeService;
        $this->mailer = $mailer;
        $this->calendarLinkService = $calendarLinkService;
    }

    /**
     * Affiche la page de paiement pour un rendez-vous spécifique.
     */
    #[Route('/{id}/pay', name: 'appointment_pay', methods: ['GET'])]
    public function pay(int $id): Response
    {
        $appointment = $this->entityManager->getRepository(Appointment::class)->find($id);

        if (!$appointment) {
            $this->addFlash('error', 'Rendez-vous non trouvé.');
            return $this->redirectToRoute('profile_index');
        }

        if ($appointment->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Accès refusé.');
            return $this->redirectToRoute('profile_index');
        }

        // Récupérer le statut 'approuvé'
        $approvedStatus = $this->entityManager->getRepository(Status::class)->findOneBy(['name' => 'approuvé']);
        if (!$approvedStatus) {
            throw $this->createNotFoundException("Le statut 'approuvé' n'a pas été trouvé dans la base de données.");
        }

        if ($appointment->getStatus()->getName() !== $approvedStatus->getName()) {
            $this->addFlash('error', 'Rendez-vous non éligible au paiement.');
            return $this->redirectToRoute('profile_index');
        }

        if ($appointment->getPayment() !== null) {
            $this->addFlash('error', 'Rendez-vous déjà payé.');
            return $this->redirectToRoute('profile_index');
        }

        return $this->render('appointment/pay.html.twig', [
            'appointment' => $appointment,
            'publicKey' => $this->stripeService->getPublicKey(),
        ]);
    }

    /**
     * Gère la création de la session de paiement Stripe.
     */
    #[Route('/{id}', name: 'appointment_payment', methods: ['POST'])]
    public function payment(Request $request, int $id): Response
    {
        $appointment = $this->entityManager->getRepository(Appointment::class)->find($id);

        if (!$appointment) {
            return $this->json(['error' => 'Rendez-vous non trouvé.'], 404);
        }

        if ($appointment->getUser() !== $this->getUser()) {
            return $this->json(['error' => 'Accès refusé.'], 403);
        }

        // Récupérer le statut 'approuvé'
        $approvedStatus = $this->entityManager->getRepository(Status::class)->findOneBy(['name' => 'approuvé']);
        if (!$approvedStatus) {
            return $this->json(['error' => "Le statut 'approuvé' n'a pas été trouvé."], 500);
        }

        if ($appointment->getStatus()->getName() !== $approvedStatus->getName()) {
            return $this->json(['error' => 'Rendez-vous non éligible au paiement.'], 400);
        }

        if ($appointment->getPayment() !== null) {
            return $this->json(['error' => 'Rendez-vous déjà payé.'], 400);
        }

        try {
            $session = $this->stripeService->createCheckoutSession($appointment);
        } catch (\Exception $e) {
            $this->logger->error('Erreur Stripe: ' . $e->getMessage());
            return $this->json(['error' => 'Erreur lors de la création de la session de paiement.'], 500);
        }

        return $this->json(['id' => $session->id]);
    }

    /**
     * Gère le succès du paiement.
     */
    #[Route('/success', name: 'appointment_payment_success', methods: ['GET'])]
    public function success(Request $request): Response
    {
        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            $this->addFlash('error', 'Session de paiement invalide.');
            return $this->redirectToRoute('profile_index');
        }

        try {
            $session = $this->stripeService->retrieveSession($sessionId);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération de la session Stripe : ' . $e->getMessage());
            $this->addFlash('error', 'Une erreur est survenue lors de la récupération de la session de paiement.');
            return $this->redirectToRoute('profile_index');
        }

        $appointmentId = $session->metadata->appointment_id ?? null;
        $appointment = $this->entityManager->getRepository(Appointment::class)->find($appointmentId);
        if (!$appointment || $appointment->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Rendez-vous introuvable ou accès refusé.');
            return $this->redirectToRoute('profile_index');
        }

        $payment = new Payment();
        $payment->setAmount($session->amount_total / 100);
        $payment->setPaymentDate(new \DateTime());
        $payment->setAppointment($appointment);

        // Récupérer le statut 'payé'
        $paidStatus = $this->entityManager->getRepository(Status::class)->findOneBy(['name' => 'payé']);
        if (!$paidStatus) {
            $this->addFlash('error', "Le statut 'payé' n'a pas été trouvé.");
            return $this->redirectToRoute('profile_index');
        }

        // Mise à jour du statut du paiement
        $payment->setStatus($paidStatus);

        // Mise à jour du statut du rendez-vous en 'payé'
        $appointment->setStatus($paidStatus);

        $this->entityManager->persist($payment);
        $this->entityManager->persist($appointment);
        $this->entityManager->flush();

        $this->sendConfirmationEmail($appointment);

        $this->addFlash('success', 'Votre paiement a été effectué avec succès.');

        return $this->redirectToRoute('profile_index');
    }

    /**
     * Gère l'annulation du paiement.
     */
    #[Route('/cancel', name: 'appointment_payment_cancel', methods: ['GET'])]
    public function cancel(): Response
    {
        $this->addFlash('error', 'Le paiement a été annulé.');
        return $this->redirectToRoute('profile_index');
    }

    private function sendConfirmationEmail(Appointment $appointment): void
    {
        $user = $appointment->getUser();
        $adminEmail = 'admin@example.com';
    
        // Récupère la date de début et la convertit en DateTimeImmutable si nécessaire
        $start = $appointment->getAppointmentDate();
        if (!$start instanceof \DateTimeImmutable) {
            if ($start instanceof \DateTime) {
                $start = \DateTimeImmutable::createFromMutable($start);
            } else {
                throw new \RuntimeException("La date du rendez-vous est invalide.");
            }
        }
    
        // Utilisation de `add` pour ajouter une heure
        $end = $start->add(new \DateInterval('PT1H'));
    
        $title = 'Rendez-vous de ' . $user->getFirstName();
        $description = 'Rendez-vous pour ' . $appointment->getService()->getName();
    
        $googleLink = $this->calendarLinkService->generateGoogleCalendarLink($start, $end, $title, $description);
        $icalLink = $this->calendarLinkService->generateIcalLink($start, $end, $title, $description);
    
        // Envoi de l'email au client
        $emailClient = (new Email())
            ->from('no-reply@example.com')
            ->to($user->getEmail())
            ->subject('Confirmation de votre rendez-vous')
            ->html($this->renderView('emails/appointment_confirmation.html.twig', [
                'appointment' => $appointment,
                'googleLink' => $googleLink,
                'icalLink' => $icalLink,
            ]));
        $this->mailer->send($emailClient);
    
        // Envoi de l'email à l'administrateur
        $emailAdmin = (new Email())
            ->from('no-reply@example.com')
            ->to($adminEmail)
            ->subject('Nouveau rendez-vous confirmé')
            ->html($this->renderView('emails/appointment_admin_notification.html.twig', [
                'appointment' => $appointment,
                'googleLink' => $googleLink,
                'icalLink' => $icalLink,
            ]));
        $this->mailer->send($emailAdmin);
    }
        
}
