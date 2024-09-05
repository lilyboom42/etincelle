<?php

namespace App\Entity;

use App\Repository\ProductCustomizationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductCustomizationRepository::class)]
class ProductCustomization
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'productCustomizations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le produit ne doit pas être nul.")]
    private ?Product $product = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le nom de la personnalisation ne doit pas être vide.")]
    #[Assert\Length(max: 50, maxMessage: "Le nom de la personnalisation ne doit pas dépasser {{ limit }} caractères.")]
    private ?string $customizationName = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "L'option de personnalisation ne doit pas être vide.")]
    private ?string $customizationOption = null;

    // Get the ID of the product customization
    // Obtenir l'ID de la personnalisation du produit
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

    // Get the customization name
    // Obtenir le nom de la personnalisation
    public function getCustomizationName(): ?string
    {
        return $this->customizationName;
    }

    // Set the customization name
    // Définir le nom de la personnalisation
    public function setCustomizationName(string $customizationName): static
    {
        $this->customizationName = $customizationName;

        return $this;
    }

    // Get the customization option
    // Obtenir l'option de personnalisation
    public function getCustomizationOption(): ?string
    {
        return $this->customizationOption;
    }

    // Set the customization option
    // Définir l'option de personnalisation
    public function setCustomizationOption(string $customizationOption): static
    {
        $this->customizationOption = $customizationOption;

        return $this;
    }

    /**
     * Returns a full description of the product customization.
     * Retourne une description complète de la personnalisation du produit.
     *
     * @return string
     */
    public function getFullDescription(): string
    {
        return sprintf(
            'Customization: %s - Option: %s',
            $this->customizationName,
            $this->customizationOption
        );
    }
}
