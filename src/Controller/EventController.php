<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Entity\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class EventController extends AbstractController
{
    #[Route('/event', name: 'event_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $events = $entityManager->getRepository(Event::class)->findAll();

        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/event/new', name: 'event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile[] $mediaFiles */
            $mediaFiles = $form->get('media')->getData(); // Utilisez 'media' ici
    
            if ($mediaFiles) {
                foreach ($mediaFiles as $mediaFile) {
                    if ($mediaFile instanceof UploadedFile) {
                        $media = new Media();
                        $media->setMediaFile($mediaFile);
    
                        $event->addMedia($media); // Utilisez addMedia() pour maintenir la relation
                    }
                }
            }
    
            $entityManager->persist($event);
            $entityManager->flush();
    
            $this->addFlash('success', 'Événement créé avec succès.');
            return $this->redirectToRoute('event_index');
        }
    
        return $this->render('event/new.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'isEdit' => false,
        ]);
    }
    

    #[Route('/event/{id}/edit', name: 'event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile[] $mediaFiles */
            $mediaFiles = $form->get('media')->getData();

            if ($mediaFiles) {
                foreach ($mediaFiles as $mediaFile) {
                    // Créer une nouvelle entité Media si des fichiers ont été ajoutés
                    $media = new Media();
                    $media->setEvent($event);
                    
                    // VichUploaderBundle gère l'upload du fichier en utilisant la propriété mediaFile
                    $media->setMediaFile($mediaFile);

                    $entityManager->persist($media);
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Événement mis à jour avec succès.');
            return $this->redirectToRoute('event_index');
        }

        return $this->render('event/new.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'isEdit' => true, // Mode édition
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
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();

            $this->addFlash('success', 'Événement supprimé avec succès.');
        }

        return $this->redirectToRoute('event_index');
    }
}
