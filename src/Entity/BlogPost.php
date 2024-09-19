<?php

namespace App\Entity;

use App\Repository\BlogPostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BlogPostRepository::class)]
#[ORM\HasLifecycleCallbacks]
class BlogPost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le titre de l'article ne doit pas être vide.")]
    #[Assert\Length(max: 100, maxMessage: "Le titre de l'article ne doit pas dépasser {{ limit }} caractères.")]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: "Le contenu de l'article ne doit pas être vide.")]
    private ?string $content = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'blogPosts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La date de création ne doit pas être nulle.")]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La date de mise à jour ne doit pas être nulle.")]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToMany(targetEntity: BlogPostCategory::class, inversedBy: 'blogPosts', cascade: ['persist'])]
    private Collection $categories;

    #[ORM\OneToMany(mappedBy: 'blogPost', targetEntity: Media::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $media;

    #[ORM\OneToMany(mappedBy: 'blogPost', targetEntity: BlogPostCategoryRelation::class)]
    private Collection $blogPostCategoryRelations;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->media = new ArrayCollection();
        $this->blogPostCategoryRelations = new ArrayCollection();
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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(BlogPostCategory $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->getBlogPosts()->add($this); // Synchronisation inverse
        }
        return $this;
    }

    public function removeCategory(BlogPostCategory $category): static
    {
        if ($this->categories->removeElement($category)) {
            $category->getBlogPosts()->removeElement($this); // Synchronisation inverse
        }
        return $this;
    }

    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedia(Media $media): static
    {
        if (!$this->media->contains($media)) {
            $this->media->add($media);
            $media->setBlogPost($this);
        }
        return $this;
    }

    public function removeMedia(Media $media): static
    {
        if ($this->media->removeElement($media)) {
            if ($media->getBlogPost() === $this) {
                $media->setBlogPost(null);
            }
        }
        return $this;
    }

    public function getBlogPostCategoryRelations(): Collection
    {
        return $this->blogPostCategoryRelations;
    }

    public function addBlogPostCategoryRelation(BlogPostCategoryRelation $relation): static
    {
        if (!$this->blogPostCategoryRelations->contains($relation)) {
            $this->blogPostCategoryRelations->add($relation);
            $relation->setBlogPost($this); // Synchronisation inverse
        }
        return $this;
    }

    public function removeBlogPostCategoryRelation(BlogPostCategoryRelation $relation): static
    {
        if ($this->blogPostCategoryRelations->removeElement($relation)) {
            if ($relation->getBlogPost() === $this) {
                $relation->setBlogPost(null); // Synchronisation inverse
            }
        }
        return $this;
    }
}
