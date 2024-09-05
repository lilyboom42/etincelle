<?php

namespace App\Entity;

use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
#[ORM\HasLifecycleCallbacks]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La date de création ne doit pas être nulle.")]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La date de mise à jour ne doit pas être nulle.")]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'utilisateur associé à la commande ne doit pas être nul.")]
    private ?User $user = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le montant total de la commande ne doit pas être nul.")]
    #[Assert\PositiveOrZero(message: "Le montant total de la commande doit être positif ou nul.")]
    private ?int $total = null;

    #[ORM\Column(type: 'string', enumType: OrderStatus::class)]
    #[Assert\NotNull(message: "Le statut de la commande ne doit pas être nul.")]
    private OrderStatus $orderStatus;

    #[ORM\OneToMany(targetEntity: OrderLine::class, mappedBy: 'orders', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $orderLines;

    public function __construct()
    {
        $this->orderLines = new ArrayCollection();
    }

    // Set the creation date before persisting.
    // Définir la date de création avant la persistance.
    #[ORM\PrePersist]
    public function setCreationDate(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Set the update date before updating.
    // Définir la date de mise à jour avant la mise à jour.
    #[ORM\PreUpdate]
    public function setUpdateDate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Get the ID of the order.
    // Obtenir l'ID de la commande.
    public function getId(): ?int
    {
        return $this->id;
    }

    // Get the creation date of the order.
    // Obtenir la date de création de la commande.
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    // Get the update date of the order.
    // Obtenir la date de mise à jour de la commande.
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Set the update date of the order.
    // Définir la date de mise à jour de la commande.
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    // Get the associated user of the order.
    // Obtenir l'utilisateur associé à la commande.
    public function getUser(): ?User
    {
        return $this->user;
    }

    // Set the associated user of the order.
    // Définir l'utilisateur associé à la commande.
    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    // Get the total amount of the order.
    // Obtenir le montant total de la commande.
    public function getTotal(): ?int
    {
        return $this->total;
    }

    // Set the total amount of the order.
    // Définir le montant total de la commande.
    public function setTotal(int $total): static
    {
        $this->total = $total;

        return $this;
    }

    // Get the status of the order.
    // Obtenir le statut de la commande.
    public function getOrderStatus(): OrderStatus
    {
        return $this->orderStatus;
    }

    // Set the status of the order.
    // Définir le statut de la commande.
    public function setOrderStatus(OrderStatus $orderStatus): static
    {
        $this->orderStatus = $orderStatus;

        return $this;
    }

    // Get the lines of the order.
    // Obtenir les lignes de la commande.
    public function getOrderLines(): Collection
    {
        return $this->orderLines;
    }

    // Add a line to the order.
    // Ajouter une ligne à la commande.
    public function addOrderLine(OrderLine $orderLine): static
    {
        if (!$this->orderLines->contains($orderLine)) {
            $this->orderLines->add($orderLine);
            $orderLine->setOrder($this);
        }

        return $this;
    }

    // Remove a line from the order.
    // Supprimer une ligne de la commande.
    public function removeOrderLine(OrderLine $orderLine): static
    {
        if ($this->orderLines->removeElement($orderLine)) {
            if ($orderLine->getOrder() === $this) {
                $orderLine->setOrder(null);
            }
        }

        return $this;
    }
}
