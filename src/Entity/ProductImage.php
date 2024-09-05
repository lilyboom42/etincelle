<?php

namespace App\Entity;

use App\Repository\ProductImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductImageRepository::class)]
class ProductImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'productImages')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    #[Assert\NotNull(message: "Le produit associé ne doit pas être nul.")]
    private ?Product $product = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le chemin de l'image ne doit pas être vide.")]
    #[Assert\Length(max: 255, maxMessage: "Le chemin de l'image ne doit pas dépasser {{ limit }} caractères.")]
    #[Assert\Url(message: "Le chemin de l'image doit être une URL valide.")]
    private ?string $imagesPath = null;

    // Get the ID of the product image
    // Obtenir l'ID de l'image du produit
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

    // Get the image path
    // Obtenir le chemin de l'image
    public function getImagesPath(): ?string
    {
        return $this->imagesPath;
    }

    // Set the image path
    // Définir le chemin de l'image
    public function setImagesPath(string $imagesPath): static
    {
        $this->imagesPath = $imagesPath;

        return $this;
    }
}
