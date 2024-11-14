<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Entity\Media;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Subscriber;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EventController extends AbstractController
{
    #[Route('/event', name: 'event_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
    {
        // Récupérer la liste des événements
        $query = $entityManager->getRepository(Event::class)->createQueryBuilder('e')->getQuery();

        // Pagination avec KnpPaginatorBundle
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        return $this->render('event/index.html.twig', [
            'pagination' => $pagination, // Envoie la variable pagination au template
        ]);
    }

    #[Route('/event/new', name: 'event_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Traitement des fichiers uploadés pour les médias
            /** @var UploadedFile[] $mediaFiles */
            $mediaFiles = $form->get('mediaFiles')->getData(); // Utilisation de 'mediaFiles'

            if ($mediaFiles) {
                foreach ($mediaFiles as $mediaFile) {
                    if ($mediaFile instanceof UploadedFile) {
                        $media = new Media();
                        $media->setEvent($event);
                        $media->setMediaFile($mediaFile); // Utilisation de VichUploader pour gérer l'upload
                        $event->addMedia($media);
                        $entityManager->persist($media);
                    }
                }
            }

            $entityManager->persist($event);
            $entityManager->flush();

            // Envoyer des notifications aux abonnés
            $this->sendNotification($mailer, $entityManager, $event, 'Créé');

            $this->addFlash('success', 'Événement créé avec succès.');
            return $this->redirectToRoute('event_index');
        }

        return $this->render('event/new.html.twig', [
            'form' => $form->createView(),
            'isEdit' => false,
        ]);
    }

    #[Route('/media/{id}/delete', name: 'media_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteMedia(Request $request, Media $media, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        // Vérifier le token CSRF
        if ($this->isCsrfTokenValid('delete' . $media->getId(), $request->request->get('_token'))) {
            $event = $media->getEvent();
            $entityManager->remove($media);
            $entityManager->flush();

            // Envoyer des notifications aux abonnés
            $this->sendNotification($mailer, $entityManager, $event, 'Supprimé');

            $this->addFlash('success', 'Média supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('event_show', ['id' => $media->getEvent()->getId()]);
    }

    #[Route('/event/{id}/edit', name: 'event_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Traitement des fichiers uploadés pour les médias
            /** @var UploadedFile[] $mediaFiles */
            $mediaFiles = $form->get('mediaFiles')->getData(); // Utilisation de 'mediaFiles'

            if ($mediaFiles) {
                foreach ($mediaFiles as $mediaFile) {
                    if ($mediaFile instanceof UploadedFile) {
                        $media = new Media();
                        $media->setEvent($event);
                        $media->setMediaFile($mediaFile); // Utilisation de VichUploader pour gérer l'upload
                        $event->addMedia($media);
                        $entityManager->persist($media);
                    }
                }
            }

            $entityManager->flush();

            // Envoyer des notifications aux abonnés
            $this->sendNotification($mailer, $entityManager, $event, 'Modifié');

            $this->addFlash('success', 'Événement modifié avec succès.');
            return $this->redirectToRoute('event_index');
        }

        return $this->render('event/new.html.twig', [
            'form' => $form->createView(),
            'isEdit' => true,
            'event' => $event,
        ]);
    }

    #[Route('/event/{id}', name: 'event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/event/{id}/delete', name: 'event_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();

            // Envoyer des notifications aux abonnés
            $this->sendNotification($mailer, $entityManager, $event, 'Supprimé');

            $this->addFlash('success', 'Événement supprimé avec succès.');
        }

        return $this->redirectToRoute('event_index');
    }

    private function sendNotification(MailerInterface $mailer, EntityManagerInterface $entityManager, Event $event, string $action): void
    {
        $subscribers = $entityManager->getRepository(Subscriber::class)->findAll(); // Abonnés globaux

        foreach ($subscribers as $subscriber) {
            $email = (new Email())
                ->from('noreply@votre-domaine.com')
                ->to($subscriber->getEmail())
                ->subject('Événement ' . $action . ' : ' . $event->getTitle())
                ->html($this->renderView('emails/event_notification.html.twig', [
                    'event' => $event,
                    'action' => $action,
                ]));

            $mailer->send($email);
        }
    }

    #[Route('/event/subscribe/{id}', name: 'event_subscribe', methods: ['GET', 'POST'])]
    public function subscribe(int $id, Request $request, EntityManagerInterface $entityManager, ?UserInterface $user): Response
    {
        $event = $entityManager->getRepository(Event::class)->find($id);

        if (!$event) {
            $this->addFlash('error', 'Événement introuvable.');
            return $this->redirectToRoute('event_index');
        }

        $subscriber = new Subscriber();

        // Vérifier si l'utilisateur est connecté, sinon, demander l'email
        if ($user) {
            // Si l'utilisateur est connecté, l'abonner automatiquement
            $existingSubscription = $entityManager->getRepository(Subscriber::class)
                ->findOneBy(['event' => $event, 'user' => $user]);

            if ($existingSubscription) {
                $this->addFlash('info', 'Vous êtes déjà abonné à cet événement.');
                return $this->redirectToRoute('event_index');
            }

            $subscriber->setUser($user);
        } else {
            // Pour un utilisateur non connecté, demander son email
            $form = $this->createFormBuilder($subscriber)
                ->add('email', EmailType::class, [
                    'label' => 'Votre adresse email',
                    'required' => true,
                ])
                ->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Vérifier si cet email est déjà abonné
                $existingSubscription = $entityManager->getRepository(Subscriber::class)
                    ->findOneBy(['event' => $event, 'email' => $subscriber->getEmail()]);

                if ($existingSubscription) {
                    $this->addFlash('info', 'Cet email est déjà abonné à cet événement.');
                    return $this->redirectToRoute('event_index');
                }

                // Enregistrer l'abonnement
                $subscriber->setSubscribedAt(new \DateTimeImmutable());
                $entityManager->persist($subscriber);
                $entityManager->flush();

                $this->addFlash('success', 'Vous êtes maintenant abonné à cet événement.');
                return $this->redirectToRoute('event_index');
            }

            return $this->render('subscription/subscribe.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        // Abonner l'utilisateur connecté
        $subscriber->setSubscribedAt(new \DateTimeImmutable());

        $entityManager->persist($subscriber);
        $entityManager->flush();

        $this->addFlash('success', 'Vous êtes maintenant abonné à cet événement.');
        return $this->redirectToRoute('event_index');
    }

    #[Route('/event/unsubscribe/{id}', name: 'event_unsubscribe', methods: ['GET'])]
    public function unsubscribe(int $id, EntityManagerInterface $entityManager, UserInterface $user): Response
    {
        $event = $entityManager->getRepository(Event::class)->find($id);

        if (!$event) {
            $this->addFlash('error', 'Événement introuvable.');
            return $this->redirectToRoute('event_index');
        }

        // Trouver l'abonnement de l'utilisateur à l'événement
        $subscription = $entityManager->getRepository(Subscriber::class)
            ->findOneBy(['event' => $event, 'user' => $user]);

        if ($subscription) {
            $entityManager->remove($subscription);
            $entityManager->flush();
            $this->addFlash('success', 'Vous vous êtes désabonné de cet événement.');
        } else {
            $this->addFlash('error', 'Vous n\'êtes pas abonné à cet événement.');
        }

        return $this->redirectToRoute('event_index');
    }
}
