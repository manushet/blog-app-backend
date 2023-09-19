<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use App\Model\TimestampableTrait;
use ApiPlatform\Metadata\ApiResource;
use App\Model\TimestampableInterface;
use App\Repository\CommentRepository;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\AuthoredEntityInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;

#[ApiResource(    
    normalizationContext: [
        'enable_max_depth' => true, 
        'groups' => [
            'read', 
            'comment:write', 
            'comment:read', 
            'comment:edit',
            'post:comment:read']
    ],
    denormalizationContext: ['groups' => ['comment:write', 'comment:edit']],
    shortName: 'comments',
    description: 'List of blog posts',      
    operations: [
        new Get(
            normalizationContext: ['enable_max_depth' => true, 'groups' => ['read', 'comment:read']],
        ),
        new Patch(
            denormalizationContext: ['groups' => ['comment:edit']],
            security: "is_granted('ROLE_MODERATOR') or object.getAuthor() == user",
            securityMessage: 'You have no access to the action',
        ),
        new Delete(
            security: "is_granted('ROLE_MODERATOR') or object.getAuthor() == user",
            securityMessage: 'You have no access to the action',
        ),
        new GetCollection(
            normalizationContext: [
                'enable_max_depth' => true, 
                'groups' => ['read', 'comment:read']
        ],
        ),
        new Post(
            denormalizationContext: ['groups' => ['comment:write']],
            security: "is_granted('ROLE_USER')",
            securityMessage: 'You have no access to the action',
        ),
    ]
)]
#[ApiResource(
    normalizationContext: [
        'enable_max_depth' => true, 
        'groups' => [
            'read', 
            'post:comment:read']
    ],    
    uriTemplate: '/posts/{id}/comments', 
    uriVariables: [
        'id' => new Link(
            fromClass: BlogPost::class,
            fromProperty: 'comments'
        )
    ], 
    operations: [
        new GetCollection(
            normalizationContext: [
                'enable_max_depth' => true, 
                'groups' => ['read', 'post:comment:read']
            ],
        ),
    ]
)]
#[ApiFilter(OrderFilter::class, properties: ['updatedAt' => 'DESC'])]
#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[HasLifecycleCallbacks]
class Comment implements AuthoredEntityInterface, TimestampableInterface
{
    use TimestampableTrait;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['comment:read', 'comment:write', 'comment:edit', 'post:read', 'post:comment:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2)]
    #[Groups(['comment:read', 'comment:write', 'comment:edit', 'post:comment:read'])]
    private ?string $content = null;

    #[ORM\Column]
    #[Groups(['comment:read', 'comment:write', 'comment:edit', 'post:comment:read'])]
    private ?bool $isPublished = true;
  
    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['comment:read', 'post:comment:read'])]
    #[MaxDepth(1)]
    /**
     * @param User $author
     */
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    #[Groups(['comment:read', 'comment:write'])]
    #[MaxDepth(1)]
    /**
     * @param BlogPost $post
     */
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
