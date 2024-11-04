<?php

// src/Service/EventNotificationService.php

namespace App\Service;

use App\Repository\SubscriberRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EventNotificationService
{
    private SubscriberRepository $subscriberRepository;
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(SubscriberRepository $subscriberRepository, MailerInterface $mailer, Environment $twig)
    {
        $this->subscriberRepository = $subscriberRepository;
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function notifySubscribers($event): void
    {
        $subscribers = $this->subscriberRepository->findAll();

        foreach ($subscribers as $subscriber) {
            $email = (new Email())
                ->from('noreply@votre-domaine.com')
                ->to($subscriber->getEmail())
                ->subject('Nouvel événement: ' . $event->getTitle())
                ->html($this->twig->render('emails/new_event.html.twig', [
                    'event' => $event,
                ]));

            $this->mailer->send($email);
        }
    }
}
