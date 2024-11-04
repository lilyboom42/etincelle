<?php
// src/MessageHandler/SendEventNotificationHandler.php

namespace App\MessageHandler;

use App\Message\SendEventNotification;
use App\Repository\EventRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class SendEventNotificationHandler
{
    private EventRepository $eventRepository;
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(EventRepository $eventRepository, MailerInterface $mailer, Environment $twig)
    {
        $this->eventRepository = $eventRepository;
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    // La méthode __invoke permet à Messenger de détecter automatiquement ce handler
    public function __invoke(SendEventNotification $message)
    {
        $event = $this->eventRepository->find($message->getEventId());

        if (!$event) {
            // Gérer le cas où l'événement n'est pas trouvé
            return;
        }

        $email = (new Email())
            ->from('noreply@votre-domaine.com')
            ->to($message->getSubscriberEmail())
            ->subject('Nouvel événement : ' . $event->getTitle())
            ->html($this->twig->render('emails/new_event.html.twig', [
                'event' => $event,
            ]));

        $this->mailer->send($email);
    }
}
