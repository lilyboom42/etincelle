<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le nom du produit ne doit pas être vide.")]
    #[Assert\Length(max: 50, maxMessage: "Le nom du produit ne doit pas dépasser {{ limit }} caractères.")]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description ne doit pas être vide.")]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: "Le prix ne doit pas être vide.")]
    #[Assert\GreaterThan(value: 0, message: "Le prix doit être supérieur à 0.")]
    private ?float $price = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La quantité en stock ne doit pas être nulle.")]
    #[Assert\PositiveOrZero(message: "La quantité en stock doit être positive ou zéro.")]
    private ?int $stockQuantity = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La date de création ne doit pas être nulle.")]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La date de mise à jour ne doit pas être nulle.")]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductCustomization::class, cascade: ['persist', 'remove'])]
    private Collection $productCustomizations;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductImage::class, cascade: ['persist', 'remove'])]
    private Collection $productImages;

    #[ORM\OneToMany(targetEntity: OrderLine::class, mappedBy: 'product', orphanRemoval: true)]
    private Collection $orderLines;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'favorites')]
    private Collection $favoritedBy;

    public function __construct()
    {
        $this->productCustomizations = new ArrayCollection();
        $this->productImages = new ArrayCollection();
        $this->orderLines = new ArrayCollection();
        $this->favoritedBy = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreationDate(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdateDate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Get the product ID
    // Obtenir l'ID du produit
    public function getId(): ?int
    {
        return $this->id;
    }

    // Get the product name
    // Obtenir le nom du produit
    public function getName(): ?string
    {
        return $this->name;
    }

    // Set the product name
    // Définir le nom du produit
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    // Get the product description
    // Obtenir la description du produit
    public function getDescription(): ?string
    {
        return $this->description;
    }

    // Set the product description
    // Définir la description du produit
    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    // Get the product price
    // Obtenir le prix du produit
    public function getPrice(): ?float
    {
        return $this->price;
    }

    // Set the product price
    // Définir le prix du produit
    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    // Get the stock quantity
    // Obtenir la quantité en stock
    public function getStockQuantity(): ?int
    {
        return $this->stockQuantity;
    }

    // Set the stock quantity
    // Définir la quantité en stock
    public function setStockQuantity(int $stockQuantity): static
    {
        $this->stockQuantity = $stockQuantity;

        return $this;
    }

    // Get the creation date
    // Obtenir la date de création
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    // Get the update date
    // Obtenir la date de mise à jour
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Get the product customizations
    // Obtenir les personnalisations du produit
    public function getProductCustomizations(): Collection
    {
        return $this->productCustomizations;
    }

    // Add a product customization
    // Ajouter une personnalisation au produit
    public function addProductCustomization(ProductCustomization $productCustomization): static
    {
        if (!$this->productCustomizations->contains($productCustomization)) {
            $this->productCustomizations->add($productCustomization);
            $productCustomization->setProduct($this);
        }

        return $this;
    }

    // Remove a product customization
    // Supprimer une personnalisation du produit
    public function removeProductCustomization(ProductCustomization $productCustomization): static
    {
        if ($this->productCustomizations->removeElement($productCustomization)) {
            if ($productCustomization->getProduct() === $this) {
                $productCustomization->setProduct(null);
            }
        }

        return $this;
    }

    // Get the product images
    // Obtenir les images du produit
    public function getProductImages(): Collection
    {
        return $this->productImages;
    }

    // Add a product image
    // Ajouter une image au produit
    public function addProductImage(ProductImage $productImage): static
    {
        if (!$this->productImages->contains($productImage)) {
            $this->productImages->add($productImage);
            $productImage->setProduct($this);
        }

        return $this;
    }

    // Remove a product image
    // Supprimer une image du produit
    public function removeProductImage(ProductImage $productImage): static
    {
        if ($this->productImages->removeElement($productImage)) {
            if ($productImage->getProduct() === $this) {
                $productImage->setProduct(null);
            }
        }

        return $this;
    }

    // Get the order lines associated with the product
    // Obtenir les lignes de commande associées au produit
    public function getOrderLines(): Collection
    {
        return $this->orderLines;
    }

    // Add an order line
    // Ajouter une ligne de commande
    public function addOrderLine(OrderLine $orderLine): static
    {
        if (!$this->orderLines->contains($orderLine)) {
            $this->orderLines->add($orderLine);
            $orderLine->setProduct($this);
        }

        return $this;
    }

    // Remove an order line
    // Supprimer une ligne de commande
    public function removeOrderLine(OrderLine $orderLine): static
    {
        if ($this->orderLines->removeElement($orderLine)) {
            if ($orderLine->getProduct() === $this) {
                $orderLine->setProduct(null);
            }
        }

        return $this;
    }

    // Get the users who have favorited the product
    // Obtenir les utilisateurs qui ont ajouté le produit aux favoris
    public function getFavoritedBy(): Collection
    {
        return $this->favoritedBy;
    }

    // Add a user to the favorites
    // Ajouter un utilisateur aux favoris
    public function addFavoritedBy(User $user): static
    {
        if (!$this->favoritedBy->contains($user)) {
            $this->favoritedBy->add($user);
            $user->addFavorite($this);
        }

        return $this;
    }

    // Remove a user from the favorites
    // Supprimer un utilisateur des favoris
    public function removeFavoritedBy(User $user): static
    {
        if ($this->favoritedBy->removeElement($user)) {
            $user->removeFavorite($this);
        }

        return $this;
    }
}
