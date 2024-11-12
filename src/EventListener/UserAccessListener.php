<?php

namespace App\EventListener;

use App\Entity\User;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Psr\Log\LoggerInterface;

class UserAccessListener
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        // Ici, vous pouvez ajouter le code à exécuter lors de la connexion d'un utilisateur
        $user = $event->getAuthenticationToken()->getUser();
        
        if ($user instanceof User) { // Vérification que l'objet est bien de type User
            $this->logger->info('Utilisateur connecté : ' . $user->getUserIdentifier());
        }
    }
}
