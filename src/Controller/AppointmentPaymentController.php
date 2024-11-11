<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Payment;
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
    
    #[Route('/{id}', name: 'appointment_payment', requirements: ['id' => '\d+'])]
    public function payment(int $id): Response
    {
        $appointment = $this->entityManager->getRepository(Appointment::class)->find($id);

        if (!$appointment) {
            $this->addFlash('error', 'Rendez-vous non trouvé.');
            return $this->redirectToRoute('profile_index');
        }

        if ($appointment->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce rendez-vous.');
        }

        if ($appointment->getStatus() !== 'approved') {
            $this->addFlash('error', 'Ce rendez-vous n\'est pas disponible pour le paiement.');
            return $this->redirectToRoute('profile_index');
        }

        if ($appointment->getPayment() !== null) {
            $this->addFlash('info', 'Ce rendez-vous a déjà été payé.');
            return $this->redirectToRoute('profile_index');
        }

        $session = $this->stripeService->createCheckoutSession($appointment);

        return $this->render('payment/index.html.twig', [
            'appointment' => $appointment,
            'sessionId' => $session->id,
            'publicKey' => $this->stripeService->getPublicKey(),
        ]);
    }

    #[Route('/success', name: 'appointment_payment_success')]
    public function success(Request $request): Response
    {
        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            $this->addFlash('error', 'Session de paiement invalide.');
            return $this->redirectToRoute('profile_index');
        }

        $stripe = new \Stripe\StripeClient($this->stripeService->getSecretKey());
        try {
            $session = $stripe->checkout->sessions->retrieve($sessionId);
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
        $payment->setStatus('paid');
        $payment->setPaymentDate(new \DateTime());
        $payment->setAppointment($appointment);

        $appointment->setPayment($payment);

        $this->entityManager->persist($payment);
        $this->entityManager->persist($appointment);
        $this->entityManager->flush();

        $this->sendConfirmationEmail($appointment);

        $this->addFlash('success', 'Votre paiement a été effectué avec succès.');

        return $this->redirectToRoute('profile_index');
    }

    private function sendConfirmationEmail(Appointment $appointment): void
    {
        $user = $appointment->getUser();
        $adminEmail = 'admin@example.com';

        $start = $appointment->getDatetime();
        $end = (new \DateTime($start->format('Y-m-d H:i:s')))->add(new \DateInterval('PT1H'));
        $title = 'Rendez-vous de ' . $user->getFirstName();
        $description = 'Rendez-vous pour ' . $appointment->getService()->getName();

        $googleLink = $this->calendarLinkService->generateGoogleCalendarLink($start, $end, $title, $description);
        $icalLink = $this->calendarLinkService->generateIcalLink($start, $end, $title, $description);

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

    #[Route('/cancel', name: 'appointment_payment_cancel')]
    public function cancel(): Response
    {
        $this->addFlash('error', 'Le paiement a été annulé.');
        return $this->redirectToRoute('profile_index');
    }
}
