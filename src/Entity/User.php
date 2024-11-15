<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Repository\UesrDetails;
use App\Entity\Order;
use App\Entity\CartItem;
use App\Entity\Appointment;
use App\Entity\Product;
use App\Entity\Subscriber;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "L'adresse email ne doit pas être vide.")]
    #[Assert\Email(message: "L'adresse email n'est pas valide.")]
    private ?string $email = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[Assert\Length(min: 6, minMessage: "Le mot de passe doit comporter au moins {{ limit }} caractères.")]
    private ?string $plainPassword = null;

    #[ORM\OneToOne(inversedBy: 'user', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?UserDetails $userDetail = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le prénom ne doit pas être vide.")]
    private ?string $firstName = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le nom de famille ne doit pas être vide.")]
    private ?string $lastName = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(nullable: false)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: false)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'user')]
    private Collection $orders;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: CartItem::class, cascade: ['persist', 'remove'])]
    private Collection $cartItems;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Subscriber $subscriber = null;

    #[ORM\ManyToMany(targetEntity: Product::class)]
    #[ORM\JoinTable(name: 'user_favorites')]
    private Collection $favorites;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $resetToken = null;

    #[ORM\OneToMany(targetEntity: Appointment::class, mappedBy: 'user')]
    private Collection $appointments;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->cartItems = new ArrayCollection();
        $this->favorites = new ArrayCollection();
        $this->appointments = new ArrayCollection();
    }

    // ============================
    // Getter et Setter pour deletedAt
    // ============================

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    // ============================
    // Lifecycle Callbacks
    // ============================

    #[ORM\PrePersist]
    public function setCreationDate(): void
    {
        if (!$this->createdAt) {
            $this->createdAt = new \DateTimeImmutable();
        }
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdateDate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // ============================
    // Identifiant et Email
    // ============================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    // ============================
    // Roles
    // ============================

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    // ============================
    // Password
    // ============================

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    // ============================
    // Reset Token
    // ============================

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;
        return $this;
    }

    // ============================
    // User Details
    // ============================

    public function getUserDetail(): ?UserDetails
    {
        return $this->userDetail;
    }

    public function setUserDetail(?UserDetails $userDetail): self
    {
        $this->userDetail = $userDetail;
        return $this;
    }

    // ============================
    // Prénom et Nom de Famille
    // ============================

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    // ============================
    // isActive
    // ============================

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    // ============================
    // Created At et Updated At
    // ============================

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // ============================
    // Orders
    // ============================

    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setUser($this);
        }
        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->removeElement($order)) {
            if ($order->getUser() === $this) {
                $order->setUser(null);
            }
        }
        return $this;
    }

    // ============================
    // Subscriber
    // ============================

    public function getSubscriber(): ?Subscriber
    {
        return $this->subscriber;
    }

    public function setSubscriber(?Subscriber $subscriber): self
    {
        if ($subscriber !== null && $subscriber->getUser() !== $this) {
            $subscriber->setUser($this);
        }
        $this->subscriber = $subscriber;
        return $this;
    }

    // ============================
    // Favorites
    // ============================

    public function getFavorites(): Collection
    {
        return $this->favorites;
    }

    public function addFavorite(Product $product): self
    {
        if (!$this->favorites->contains($product)) {
            $this->favorites->add($product);
        }
        return $this;
    }

    public function removeFavorite(Product $product): self
    {
        $this->favorites->removeElement($product);
        return $this;
    }

    // ============================
    // Appointments
    // ============================

    public function getAppointments(): Collection
    {
        return $this->appointments;
    }

    public function addAppointment(Appointment $appointment): self
    {
        if (!$this->appointments->contains($appointment)) {
            $this->appointments->add($appointment);
            $appointment->setUser($this);
        }
        return $this;
    }

    public function removeAppointment(Appointment $appointment): self
    {
        if ($this->appointments->removeElement($appointment)) {
            if ($appointment->getUser() === $this) {
                $appointment->setUser(null);
            }
        }
        return $this;
    }

    // ============================
    // CartItems
    // ============================

    /**
     * @return Collection<int, CartItem>
     */
    public function getCartItems(): Collection
    {
        return $this->cartItems;
    }

    public function addCartItem(CartItem $cartItem): self
    {
        if (!$this->cartItems->contains($cartItem)) {
            $this->cartItems->add($cartItem);
            $cartItem->setUser($this);
        }

        return $this;
    }

    public function removeCartItem(CartItem $cartItem): self
    {
        if ($this->cartItems->removeElement($cartItem)) {
            if ($cartItem->getUser() === $this) {
                $cartItem->setUser(null);
            }
        }

        return $this;
    }

public function getUsername(): string
{
    return $this->getUserIdentifier();
}
}
