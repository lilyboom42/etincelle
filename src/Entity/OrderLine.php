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

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
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
    public function getId(): ?int
    {
        return $this->id;
    }

    // Get the quantity of the order line.
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    // Set the quantity of the order line.
    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    // Get the price of the order line.
    public function getPrice(): ?float
    {
        return $this->price;
    }

    // Set the price of the order line.
    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    // Get the product associated with the order line.
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    // Set the product associated with the order line.
    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    // Get the order associated with the order line.
    public function getOrder(): ?Order
    {
        return $this->order;
    }

    // Set the order associated with the order line.
    public function setOrder(?Order $order): static
    {
        $this->order = $order;

        return $this;
    }
}
