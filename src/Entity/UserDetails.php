<?php

namespace App\Entity;

use App\Repository\UserDetailsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserDetailsRepository::class)]
class UserDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "La ville ne doit pas être vide.")]
    #[Assert\Length(max: 50, maxMessage: "La ville ne doit pas dépasser {{ limit }} caractères.")]
    private ?string $city = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: "L'adresse ne doit pas être vide.")]
    #[Assert\Length(max: 150, maxMessage: "L'adresse ne doit pas dépasser {{ limit }} caractères.")]
    private ?string $address = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le pays ne doit pas être vide.")]
    #[Assert\Length(max: 50, maxMessage: "Le pays ne doit pas dépasser {{ limit }} caractères.")]
    private ?string $country = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: "Le code postal ne doit pas être nul.")]
    #[Assert\Length(min: 4, max: 10, minMessage: "Le code postal doit comporter au moins {{ limit }} chiffres.", maxMessage: "Le code postal ne doit pas dépasser {{ limit }} chiffres.")]
    private ?string $postalCode = null;

    #[ORM\Column(length: 15)]
    #[Assert\NotBlank(message: "Le numéro de téléphone ne doit pas être nul.")]
    #[Assert\Regex(pattern: "/^[0-9]{10}$/", message: "Le numéro de téléphone doit comporter 10 chiffres.")]
    private ?string $phoneNumber = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La date de naissance ne doit pas être nulle.")]
    #[Assert\LessThan('today', message: "La date de naissance doit être dans le passé.")]
    private ?\DateTimeImmutable $birthDate = null;

    #[ORM\OneToOne(mappedBy: 'userDetail', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    // Get the ID of the user details
    // Obtenir l'ID des détails de l'utilisateur
    public function getId(): ?int
    {
        return $this->id;
    }

    // Get the city
    // Obtenir la ville
    public function getCity(): ?string
    {
        return $this->city;
    }

    // Set the city
    // Définir la ville
    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    // Get the address
    // Obtenir l'adresse
    public function getAddress(): ?string
    {
        return $this->address;
    }

    // Set the address
    // Définir l'adresse
    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    // Get the country
    // Obtenir le pays
    public function getCountry(): ?string
    {
        return $this->country;
    }

    // Set the country
    // Définir le pays
    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    // Get the postal code
    // Obtenir le code postal
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    // Set the postal code
    // Définir le code postal
    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    // Get the phone number
    // Obtenir le numéro de téléphone
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    // Set the phone number
    // Définir le numéro de téléphone
    public function setPhoneNumber(string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    // Get the birth date
    // Obtenir la date de naissance
    public function getBirthDate(): ?\DateTimeImmutable
    {
        return $this->birthDate;
    }

    // Set the birth date
    // Définir la date de naissance
    public function setBirthDate(\DateTimeImmutable $birthDate): static
    {
        $this->birthDate = $birthDate;

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
        if ($user === null && $this->user !== null) {
            $this->user->setUserDetail(null);
        }

        if ($user !== null && $user->getUserDetail() !== $this) {
            $user->setUserDetail($this);
        }

        $this->user = $user;

        return $this;
    }
}
