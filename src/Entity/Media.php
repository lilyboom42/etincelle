<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
class Media
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: BlogPost::class, inversedBy: 'media')]
    #[ORM\JoinColumn(nullable: false)]
    private ?BlogPost $blogPost = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le chemin du média ne doit pas être vide.")]
    private ?string $path = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: ["image", "video"], message: "Le type doit être soit 'image' soit 'video'.")]
    private ?string $type = null;

    // Get the ID of the media.
    // Obtenir l'ID du média.
    public function getId(): ?int
    {
        return $this->id;
    }

    // Get the associated BlogPost of the media.
    // Obtenir l'article de blog associé au média.
    public function getBlogPost(): ?BlogPost
    {
        return $this->blogPost;
    }

    // Set the associated BlogPost of the media.
    // Définir l'article de blog associé au média.
    public function setBlogPost(?BlogPost $blogPost): static
    {
        $this->blogPost = $blogPost;

        return $this;
    }

    // Get the path of the media.
    // Obtenir le chemin du média.
    public function getPath(): ?string
    {
        return $this->path;
    }

    // Set the path of the media.
    // Définir le chemin du média.
    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    // Get the type of the media (image or video).
    // Obtenir le type du média (image ou vidéo).
    public function getType(): ?string
    {
        return $this->type;
    }

    // Set the type of the media (image or video).
    // Définir le type du média (image ou vidéo).
    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }
}
