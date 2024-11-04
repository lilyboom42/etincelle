<?php

namespace App\Controller;

use App\Entity\Subscriber;
use App\Form\SubscriberType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends AbstractController
{
    #[Route('/subscribe', name: 'subscribe_to_notifications', methods: ['GET', 'POST'])]
    public function subscribe(Request $request, EntityManagerInterface $entityManager): Response
    {
        $subscriber = new Subscriber();
        $form = $this->createForm(SubscriberType::class, $subscriber);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $subscriber->setSubscribedAt(new \DateTimeImmutable());

            // Vérifier si l'email est déjà abonné
            $existingSubscriber = $entityManager->getRepository(Subscriber::class)
                ->findOneBy(['email' => $subscriber->getEmail()]);

            if ($existingSubscriber) {
                $this->addFlash('info', 'Vous êtes déjà abonné aux notifications.');
            } else {
                // Persister l'abonné
                $entityManager->persist($subscriber);
                $entityManager->flush();
                $this->addFlash('success', 'Vous êtes maintenant abonné aux notifications.');
            }

            return $this->redirectToRoute('event_index'); // Redirection après l'abonnement
        }

        return $this->render('subscription/subscribe.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
