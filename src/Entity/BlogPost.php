<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use App\Model\TimestampableTrait;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Model\TimestampableInterface;
use App\Repository\BlogPostRepository;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\AuthoredEntityInterface;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

#[ApiResource(    
    normalizationContext: ['enable_max_depth' => true, 'groups' => ['read', 'post:write', 'post:read', 'post:edit']],
    denormalizationContext: ['groups' => ['post:write', 'post:edit']],
    shortName: 'posts',
    description: 'List of blog posts',          
    operations: [
        new Get(
            normalizationContext: ['enable_max_depth' => true, 'groups' => ['read', 'post:read']],
        ),
        new Patch(
            denormalizationContext: ['groups' => ['post:edit']],
            security: "is_granted('ROLE_EDITOR') or object.getAuthor() == user",
            securityMessage: 'You have no access to the action',
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN') or object.getAuthor() == user",
            securityMessage: 'You have no access to the action',
        ),
        new GetCollection(
            normalizationContext: ['enable_max_depth' => true, 'groups' => ['read', 'post:read']],
        ),
        new Post(
            denormalizationContext: ['groups' => ['post:write']],
            security: "is_granted('ROLE_AUTHOR')",
            securityMessage: 'You have no access to the action',
        ),
    ],
    order: ['createdAt' => 'DESC']
)]
#[ApiFilter(OrderFilter::class, properties: ['updatedAt' => 'DESC'])]
#[ApiFilter(SearchFilter::class, properties: ['title' => 'partial', 'author.username' => 'iexact', 'content' => 'partial'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt'])]
#[ORM\Entity(repositoryClass: BlogPostRepository::class)]
#[HasLifecycleCallbacks]
class BlogPost implements AuthoredEntityInterface, TimestampableInterface
{    
   
    use TimestampableTrait;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['post:read', 'comment:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['post:read', 'post:write', 'post:edit'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 255)]
    private ?string $title = null;

    #[ORM\Column]
    #[Groups(['post:read', 'post:write', 'post:edit'])]
    private ?bool $isPublished = false;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['post:read', 'post:write', 'post:edit'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 5)]
    private ?string $content = null;

    #[ORM\Column(length: 255)]
    #[Groups(['post:read', 'post:write'])]
    #[Assert\NotBlank]
    private ?string $slug = null;
     
    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['post:read'])]
    #[MaxDepth(1)]
    /**
     * @var User $author
     */
    private ?User $author = null;
 
    #[ORM\OneToMany(mappedBy: 'post', targetEntity: Comment::class, orphanRemoval: true)]
    #[Groups(['post:read'])]
    #[MaxDepth(1)]
    #[ApiProperty(readableLink: false, writableLink: false)] 
    /**
     * @var Collection|Comment $comments
     */
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'blogPost', targetEntity: Image::class, orphanRemoval: true)]
    #[Groups(['post:read'])]
    #[MaxDepth(1)]
    private Collection $images;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->images = new ArrayCollection();
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

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setBlogPost($this);
        }

        return $this;
    }

    public function removeImage(Image $image): static
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getBlogPost() === $this) {
                $image->setBlogPost(null);
            }
        }

        return $this;
    }
}
