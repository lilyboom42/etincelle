<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le titre de l'événement ne doit pas être vide.")]
    #[Assert\Length(max: 100, maxMessage: "Le titre de l'événement ne doit pas dépasser {{ limit }} caractères.")]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description de l'événement ne doit pas être vide.")]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La date de l'événement ne doit pas être nulle.")]
    #[Assert\GreaterThanOrEqual('today', message: "La date de l'événement doit être aujourd'hui ou dans le futur.")]
    private ?\DateTimeImmutable $eventDate = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La date de création ne doit pas être nulle.")]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La date de mise à jour ne doit pas être nulle.")]
    private ?\DateTimeImmutable $updatedAt = null;

    // Set creation and update dates before persisting to the database.
    // Définir les dates de création et de mise à jour avant l'enregistrement dans la base de données.
    #[ORM\PrePersist]
    public function setCreationDate(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Update the update date before updating in the database.
    // Mettre à jour la date de mise à jour avant de la mettre à jour dans la base de données.
    #[ORM\PreUpdate]
    public function setUpdateDate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Get the ID of the event.
    // Obtenir l'ID de l'événement.
    public function getId(): ?int
    {
        return $this->id;
    }

    // Get the title of the event.
    // Obtenir le titre de l'événement.
    public function getTitle(): ?string
    {
        return $this->title;
    }

    // Set the title of the event.
    // Définir le titre de l'événement.
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    // Get the description of the event.
    // Obtenir la description de l'événement.
    public function getDescription(): ?string
    {
        return $this->description;
    }

    // Set the description of the event.
    // Définir la description de l'événement.
    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    // Get the date of the event.
    // Obtenir la date de l'événement.
    public function getEventDate(): ?\DateTimeImmutable
    {
        return $this->eventDate;
    }

    // Set the date of the event.
    // Définir la date de l'événement.
    public function setEventDate(\DateTimeImmutable $eventDate): static
    {
        $this->eventDate = $eventDate;

        return $this;
    }

    // Get the creation date of the event.
    // Obtenir la date de création de l'événement.
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    // Get the update date of the event.
    // Obtenir la date de mise à jour de l'événement.
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Set the update date of the event.
    // Définir la date de mise à jour de l'événement.
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
