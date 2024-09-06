<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
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
    #[Assert\NotBlank(message: "Le mot de passe ne doit pas être vide.")]
    private ?string $password = null;

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
    private ?bool $isActive = true;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(targetEntity: BlogPost::class, mappedBy: 'author', orphanRemoval: true)]
    private Collection $blogPosts;

    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'user')]
    private Collection $orders;

    #[ORM\OneToOne(mappedBy: 'userId', cascade: ['persist', 'remove'])]
    private ?Subscribers $subscribers = null;

    #[ORM\ManyToMany(targetEntity: Product::class)]
    #[ORM\JoinTable(name: 'user_favorites')]
    private Collection $favorites;

    public function __construct()
    {
        $this->blogPosts = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->favorites = new ArrayCollection();
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

    // Get the user ID
    // Obtenir l'ID de l'utilisateur
    public function getId(): ?int
    {
        return $this->id;
    }

    // Get the email of the user
    // Obtenir l'email de l'utilisateur
    public function getEmail(): ?string
    {
        return $this->email;
    }

    // Set the email of the user
    // Définir l'email de l'utilisateur
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    // Get the unique identifier for the user
    // Obtenir l'identifiant unique de l'utilisateur
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    // Get the roles assigned to the user
    // Obtenir les rôles assignés à l'utilisateur
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    // Set the roles assigned to the user
    // Définir les rôles assignés à l'utilisateur
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    // Get the user's password
    // Obtenir le mot de passe de l'utilisateur
    public function getPassword(): ?string
    {
        return $this->password;
    }

    // Set the user's password
    // Définir le mot de passe de l'utilisateur
    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    // Clear temporary sensitive data
    // Effacer les données sensibles temporaires
    public function eraseCredentials(): void
    {
        // Effacez les données sensibles temporaires si nécessaire
    }

    // Get user details
    // Obtenir les détails de l'utilisateur
    public function getUserDetail(): ?UserDetails
    {
        return $this->userDetail;
    }

    // Set user details
    // Définir les détails de l'utilisateur
    public function setUserDetail(?UserDetails $userDetail): static
    {
        $this->userDetail = $userDetail;

        return $this;
    }

    // Get the first name of the user
    // Obtenir le prénom de l'utilisateur
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    // Set the first name of the user
    // Définir le prénom de l'utilisateur
    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    // Get the last name of the user
    // Obtenir le nom de famille de l'utilisateur
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    // Set the last name of the user
    // Définir le nom de famille de l'utilisateur
    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    // Check if the user is active
    // Vérifier si l'utilisateur est actif
    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    // Set the user as active or inactive
    // Définir l'utilisateur comme actif ou inactif
    public function setActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    // Get the creation date of the user
    // Obtenir la date de création de l'utilisateur
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    // Get the update date of the user
    // Obtenir la date de mise à jour de l'utilisateur
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Set the update date of the user
    // Définir la date de mise à jour de l'utilisateur
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    // Get all blog posts of the user
    // Obtenir tous les articles de blog de l'utilisateur
    public function getBlogPosts(): Collection
    {
        return $this->blogPosts;
    }

    // Add a blog post to the user
    // Ajouter un article de blog à l'utilisateur
    public function addBlogPost(BlogPost $blogPost): static
    {
        if (!$this->blogPosts->contains($blogPost)) {
            $this->blogPosts->add($blogPost);
            $blogPost->setAuthor($this);
        }

        return $this;
    }

    // Remove a blog post from the user
    // Supprimer un article de blog de l'utilisateur
    public function removeBlogPost(BlogPost $blogPost): static
    {
        if ($this->blogPosts->removeElement($blogPost)) {
            if ($blogPost->getAuthor() === $this) {
                $blogPost->setAuthor(null);
            }
        }

        return $this;
    }

    // Get all orders of the user
    // Obtenir toutes les commandes de l'utilisateur
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    // Add an order to the user
    // Ajouter une commande à l'utilisateur
    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setUser($this);
        }

        return $this;
    }

    // Remove an order from the user
    // Supprimer une commande de l'utilisateur
    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            if ($order->getUser() === $this) {
                $order->setUser(null);
            }
        }

        return $this;
    }

    // Get the subscriber associated with the user
    // Obtenir l'abonné associé à l'utilisateur
    public function getSubscribers(): ?Subscribers
    {
        return $this->subscribers;
    }

    // Set the subscriber associated with the user
    // Définir l'abonné associé à l'utilisateur
    public function setSubscribers(?Subscribers $subscribers): static
    {
        if ($subscribers === null && $this->subscribers !== null) {
            $this->subscribers->setUserId(null);
        }

        if ($subscribers !== null && $subscribers->getUserId() !== $this) {
            $subscribers->setUserId($this);
        }

        $this->subscribers = $subscribers;

        return $this;
    }

    /**
     * Get the user's favorite products
     * Obtenir les produits favoris de l'utilisateur
     * 
     * @return Collection<int, Product>
     */
    public function getFavorites(): Collection
    {
        return $this->favorites;
    }

    /**
     * Add a favorite product to the user
     * Ajouter un produit favori à l'utilisateur
     */
    public function addFavorite(Product $product): static
    {
        if (!$this->favorites->contains($product)) {
            $this->favorites->add($product);
        }

        return $this;
    }

    /**
     * Remove a favorite product from the user
     * Supprimer un produit favori de l'utilisateur
     */
    public function removeFavorite(Product $product): static
    {
        $this->favorites->removeElement($product);

        return $this;
    }
}
