<?php

namespace App\Entity;

use App\Entity\User;
use App\Repository\CartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartRepository::class)]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'cart', targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'cart', targetEntity: CartItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $cartItems;

    #[ORM\OneToMany(targetEntity: Appointment::class, mappedBy: 'cart', cascade: ['persist'])]
    private Collection $appointments;

    public function __construct()
    {
        $this->cartItems = new ArrayCollection();
        $this->appointments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

  

    public function getCartItems(): Collection
    {
        return $this->cartItems;
    }

    public function addCartItem(CartItem $cartItem): self
    {
        if (!$this->cartItems->contains($cartItem)) {
            $this->cartItems->add($cartItem);
            $cartItem->setCart($this);
        }
        return $this;
    }

    public function removeCartItem(CartItem $cartItem): self
    {
        if ($this->cartItems->removeElement($cartItem)) {
            if ($cartItem->getCart() === $this) {
                $cartItem->setCart(null);
            }
        }
        return $this;
    }

    public function clearItems(): void
    {
        foreach ($this->cartItems as $item) {
            $this->removeCartItem($item);
        }
    }

    /**
     * Calcule le total du panier.
     *
     * @return float Le total du panier.
     */
    public function getTotal(): float
    {
        $total = 0.0;
        foreach ($this->cartItems as $item) {
            $price = (float)$item->getProduct()->getPrice(); // Convertir le prix en float
            $quantity = $item->getQuantity();
            $total += $price * $quantity;
        }
        return $total;
    }

    /**
     * Vérifie si le panier contient des articles.
     *
     * @return bool Vrai si le panier contient au moins un article, sinon faux.
     */
    public function hasItems(): bool
    {
        return !$this->cartItems->isEmpty();
    }

    public function getAppointments(): Collection
    {
        return $this->appointments;
    }

    public function addAppointment(Appointment $appointment): self
    {
        if (!$this->appointments->contains($appointment)) {
            $this->appointments[] = $appointment;
            $appointment->setCart($this);
        }

        return $this;
    }

    public function removeAppointment(Appointment $appointment): self
    {
        if ($this->appointments->removeElement($appointment)) {
            if ($appointment->getCart() === $this) {
                $appointment->setCart(null);
            }
        }

        return $this;
    }
}
