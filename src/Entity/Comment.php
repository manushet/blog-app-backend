<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\CommentRepository;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\AuthoredEntityInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

#[ApiResource(    
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write', 'edit']],
    shortName: 'comments',
    description: 'List of blog posts',      
    operations: [
        new Get(
            normalizationContext: ['groups' => ['read']],
        ),
        new Patch(
            denormalizationContext: ['groups' => ['edit']],
            security: "is_granted('ROLE_ADMIN') or object.getAuthor() == user",
            securityMessage: 'Only admins can edit comments',
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN') or object.getAuthor() == user",
            securityMessage: 'Only admins can delete comments',
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['read']],
        ),
        new Post(
            denormalizationContext: ['groups' => ['write']],
            security: "is_granted('ROLE_USER')",
            securityMessage: 'Only users can add comments',
        ),
    ]
)]
#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment implements AuthoredEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2)]
    #[Groups(['read', 'write', 'edit'])]
    private ?string $content = null;

    #[ORM\Column]
    #[Groups(['read', 'write', 'edit'])]
    private ?bool $isPublished = true;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read'])]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    #[Groups(['read', 'write'])]
    private ?BlogPost $post = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function isIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * @param UserInterface $author
     * @return static
     */
    public function setAuthor(UserInterface $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getPost(): ?BlogPost
    {
        return $this->post;
    }

    public function setPost(?BlogPost $post): static
    {
        $this->post = $post;

        return $this;
    }
}
