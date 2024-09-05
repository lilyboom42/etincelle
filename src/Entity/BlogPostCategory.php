<?php

namespace App\Entity;

use App\Repository\BlogPostCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BlogPostCategoryRepository::class)]
class BlogPostCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 60, unique: true)]
    #[Assert\NotBlank(message: "Le nom de la catégorie ne doit pas être vide.")]
    #[Assert\Length(max: 60, maxMessage: "Le nom de la catégorie ne doit pas dépasser {{ limit }} caractères.")]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: BlogPost::class, mappedBy: 'categories')]
    private Collection $blogPosts;

    public function __construct()
    {
        // Initialize the collection of blog posts.
        // Initialiser la collection des articles de blog.
        $this->blogPosts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        // Get the ID of the blog post category.
        // Obtenir l'ID de la catégorie d'article de blog.
        return $this->id;
    }

    public function getName(): ?string
    {
        // Get the name of the blog post category.
        // Obtenir le nom de la catégorie d'article de blog.
        return $this->name;
    }

    public function setName(string $name): static
    {
        // Set the name of the blog post category.
        // Définir le nom de la catégorie d'article de blog.
        $this->name = $name;

        return $this;
    }

    /**
     * Get all blog posts associated with this category.
     * Récupérer tous les articles de blog associés à cette catégorie.
     *
     * @return Collection<int, BlogPost>
     */
    public function getBlogPosts(): Collection
    {
        return $this->blogPosts;
    }
}
