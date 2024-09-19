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

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotNull(message: "Le prix ne doit pas être nul.")]
    #[Assert\Positive(message: "Le prix doit être supérieur à zéro.")]
    private ?float $price = null;

    // Get the ID of the product customization
    public function getId(): ?int
    {
        return $this->id;
    }

    // Get the associated product
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    // Set the associated product
    public function setProduct(?Product $product): static
    {
        $this->product = $product;
        return $this;
    }

    // Get the customization name
    public function getCustomizationName(): ?string
    {
        return $this->customizationName;
    }

    // Set the customization name
    public function setCustomizationName(string $customizationName): static
    {
        $this->customizationName = $customizationName;
        return $this;
    }

    // Get the customization option
    public function getCustomizationOption(): ?string
    {
        return $this->customizationOption;
    }

    // Set the customization option
    public function setCustomizationOption(string $customizationOption): static
    {
        $this->customizationOption = $customizationOption;
        return $this;
    }

    // Get the price of the customization
    public function getPrice(): ?float
    {
        return $this->price;
    }

    // Set the price of the customization
    public function setPrice(float $price): static
    {
        $this->price = $price;
        return $this;
    }

    // Returns a full description of the product customization
    public function getFullDescription(): string
    {
        return sprintf(
            'Customization: %s - Option: %s - Price: %.2f',
            $this->customizationName,
            $this->customizationOption,
            $this->price
        );
    }
}
