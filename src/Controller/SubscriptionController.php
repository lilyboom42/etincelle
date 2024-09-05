<?php

namespace App\Controller;

use App\Repository\SubscribersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends AbstractController
{
    /**
     * Handle the unsubscribe request.
     * Gère la demande de désabonnement.
     */
    #[Route('/unsubscribe/{id}', name: 'unsubscribe')]
    public function unsubscribe(int $id, SubscribersRepository $subscribersRepository, EntityManagerInterface $entityManager): Response
    {
        // Find the subscriber by ID.
        // Trouver l'abonné par ID.
        $subscriber = $subscribersRepository->find($id);

        if ($subscriber) {
            // Remove the subscriber from the database.
            // Supprime l'abonné de la base de données.
            $entityManager->remove($subscriber);
            $entityManager->flush();
            
            return new Response('Votre désabonnement a été pris en compte.'); // Unsubscribe confirmation.
        }

        return new Response('Utilisateur non trouvé.', Response::HTTP_NOT_FOUND); // Subscriber not found.
    }
}
