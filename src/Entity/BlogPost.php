<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\BlogPostRepository;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\AuthoredEntityInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

#[ApiResource(    
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write', 'edit']],
    shortName: 'posts',
    description: 'List of blog posts',          
    operations: [
        new Get(
            normalizationContext: ['groups' => ['read']],
        ),
        new Patch(
            denormalizationContext: ['groups' => ['edit']],
            security: "is_granted('ROLE_ADMIN') or object.getAuthor() == user",
            securityMessage: 'Only admins can edit posts',
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN') or object.getAuthor() == user",
            securityMessage: 'Only admins can delete posts',
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['read']],
        ),
        new Post(
            denormalizationContext: ['groups' => ['write']],
            security: "is_granted('ROLE_USER')",
            securityMessage: 'Only users can create new posts',
        ),
    ],
)]
#[ORM\Entity(repositoryClass: BlogPostRepository::class)]
class BlogPost implements AuthoredEntityInterface
{    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read', 'write', 'edit'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 255)]
    private ?string $title = null;

    #[ORM\Column]
    #[Groups(['read', 'write', 'edit'])]
    private ?bool $isPublished = false;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['read', 'write', 'edit'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 5)]
    private ?string $content = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read', 'write'])]
    #[Assert\NotBlank]
    private ?string $slug = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read'])]
    private ?User $author = null;


    #[ORM\Column]
    #[Groups(['read'])]  
    /**
     * Created datetime 
     *
     * @var \DateTimeImmutable|null
     */    
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['read'])]
    /**
     * Last updated datetime
     *
     * @var \DateTimeImmutable|null
     */
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeImmutable $createdAt
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */    
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTimeImmutable $updatedAt
     */
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }       

    #[ORM\OneToMany(mappedBy: 'post', targetEntity: Comment::class, orphanRemoval: true)]
    private Collection $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
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

    public function isIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return User|null
     */
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

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }

}
