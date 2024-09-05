<?php

namespace App\Entity;

use App\Repository\SubscribersRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscribersRepository::class)]
class Subscribers
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $email = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $subscribedAt = null;

    #[ORM\OneToOne(inversedBy: 'subscribers', cascade: ['persist', 'remove'])]
    private ?User $userId = null;

    // Get the subscriber ID
    // Obtenir l'ID de l'abonné
    public function getId(): ?int
    {
        return $this->id;
    }

    // Get the email of the subscriber
    // Obtenir l'email de l'abonné
    public function getEmail(): ?string
    {
        return $this->email;
    }

    // Set the email of the subscriber
    // Définir l'email de l'abonné
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    // Get the subscription date
    // Obtenir la date d'abonnement
    public function getSubscribedAt(): ?\DateTimeImmutable
    {
        return $this->subscribedAt;
    }

    // Set the subscription date
    // Définir la date d'abonnement
    public function setSubscribedAt(\DateTimeImmutable $subscribedAt): static
    {
        $this->subscribedAt = $subscribedAt;

        return $this;
    }

    // Get the associated user
    // Obtenir l'utilisateur associé
    public function getUserId(): ?User
    {
        return $this->userId;
    }

    // Set the associated user
    // Définir l'utilisateur associé
    public function setUserId(?User $userId): static
    {
        $this->userId = $userId;

        return $this;
    }
}
