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

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: BlogPostCategoryRelation::class)]
    private Collection $blogPostCategoryRelations;

    public function __construct()
    {
        // Initialiser les collections
        $this->blogPosts = new ArrayCollection();
        $this->blogPostCategoryRelations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Récupérer tous les articles de blog associés à cette catégorie.
     *
     * @return Collection<int, BlogPost>
     */
    public function getBlogPosts(): Collection
    {
        return $this->blogPosts;
    }

    /**
     * Récupérer toutes les relations de catégorie d'article de blog.
     *
     * @return Collection<int, BlogPostCategoryRelation>
     */
    public function getBlogPostCategoryRelations(): Collection
    {
        return $this->blogPostCategoryRelations;
    }
}
