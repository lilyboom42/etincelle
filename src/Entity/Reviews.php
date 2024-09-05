<?php

namespace App\Entity;

use App\Repository\ReviewsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReviewsRepository::class)]
class Reviews
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le produit ne doit pas être nul.")]
    private ?Product $product = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'utilisateur ne doit pas être nul.")]
    private ?User $user = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La note ne doit pas être nulle.")]
    #[Assert\Range(
        min: 1,
        max: 5,
        notInRangeMessage: "La note doit être comprise entre {{ min }} et {{ max }}.",
    )]
    private ?int $rating = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La date de création ne doit pas être nulle.")]
    #[Assert\Type("\DateTimeImmutable", message: "La date de création doit être un objet DateTimeImmutable.")]
    private ?\DateTimeImmutable $createdAt = null;

    // Constructor to initialize the creation date
    // Constructeur pour initialiser la date de création
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // Get the review ID
    // Obtenir l'ID de l'avis
    public function getId(): ?int
    {
        return $this->id;
    }

    // Get the associated product
    // Obtenir le produit associé
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    // Set the associated product
    // Définir le produit associé
    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    // Get the associated user
    // Obtenir l'utilisateur associé
    public function getUser(): ?User
    {
        return $this->user;
    }

    // Set the associated user
    // Définir l'utilisateur associé
    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    // Get the rating
    // Obtenir la note
    public function getRating(): ?int
    {
        return $this->rating;
    }

    // Set the rating
    // Définir la note
    public function setRating(int $rating): static
    {
        $this->rating = $rating;

        return $this;
    }

    // Get the creation date
    // Obtenir la date de création
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    // Set the creation date
    // Définir la date de création
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
