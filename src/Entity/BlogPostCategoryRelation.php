<?php

namespace App\Entity;

use App\Repository\BlogPostCategoryRelationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlogPostCategoryRelationRepository::class)]
class BlogPostCategoryRelation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: BlogPost::class, inversedBy: 'blogPostCategoryRelations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?BlogPost $blogPost = null;

    #[ORM\ManyToOne(targetEntity: BlogPostCategory::class, inversedBy: 'blogPostCategoryRelations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?BlogPostCategory $category = null;
    

    public function getId(): ?int
    {
        // Get the ID of the relation.
        // Obtenir l'ID de la relation.
        return $this->id;
    }

    public function getBlogPost(): ?BlogPost
    {
        // Get the blog post associated with this relation.
        // Obtenir l'article de blog associé à cette relation.
        return $this->blogPost;
    }

    public function setBlogPost(?BlogPost $blogPost): static
    {
        // Set the blog post associated with this relation.
        // Définir l'article de blog associé à cette relation.
        $this->blogPost = $blogPost;

        return $this;
    }

    public function getCategory(): ?BlogPostCategory
    {
        // Get the category associated with this relation.
        // Obtenir la catégorie associée à cette relation.
        return $this->category;
    }

    public function setCategory(?BlogPostCategory $category): static
    {
        // Set the category associated with this relation.
        // Définir la catégorie associée à cette relation.
        $this->category = $category;

        return $this;
    }
}
