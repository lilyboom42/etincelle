<?php

namespace App\Entity;

use App\Repository\OrderLineRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderLineRepository::class)]
class OrderLine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La quantité ne doit pas être nulle.")]
    #[Assert\Positive(message: "La quantité doit être un nombre positif.")]
    private ?int $quantity = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: "Le prix ne doit pas être vide.")]
    #[Assert\PositiveOrZero(message: "Le prix doit être un nombre positif ou zéro.")]
    private ?string $price = null;

    #[ORM\ManyToOne(inversedBy: 'orderLines')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le produit associé ne doit pas être nul.")]
    private ?Product $product = null;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'orderLines')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "La commande associée ne doit pas être nulle.")]
    private ?Order $order = null;
    

    // Get the ID of the order line.
    // Obtenir l'ID de la ligne de commande.
    public function getId(): ?int
    {
        return $this->id;
    }

    // Get the quantity of the order line.
    // Obtenir la quantité de la ligne de commande.
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    // Set the quantity of the order line.
    // Définir la quantité de la ligne de commande.
    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    // Get the price of the order line.
    // Obtenir le prix de la ligne de commande.
    public function getPrice(): ?string
    {
        return $this->price;
    }

    // Set the price of the order line.
    // Définir le prix de la ligne de commande.
    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    // Get the product associated with the order line.
    // Obtenir le produit associé à la ligne de commande.
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    // Set the product associated with the order line.
    // Définir le produit associé à la ligne de commande.
    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    // Get the order associated with the order line.
    // Obtenir la commande associée à la ligne de commande.
    public function getOrder(): ?Order
    {
        return $this->order;
    }

    // Set the order associated with the order line.
    // Définir la commande associée à la ligne de commande.
    public function setOrder(?Order $order): static
    {
        $this->order = $order;

        return $this;
    }
}
