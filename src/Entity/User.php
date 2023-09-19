<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use Symfony\Component\Uid\Uuid;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ApiResource(   
    normalizationContext: ['enable_max_depth' => true, 'groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write', 'user:edit']],
    shortName: 'users',
    description: 'List of active users',     
    #types: ['https://schema.org/Offer']
    #paginationItemsPerPage: 25      
    operations: [
        new Get(
            normalizationContext: ['enable_max_depth' => true, 'groups' => ['user:read']],
            security: "is_granted('ROLE_MODERATOR') or object == user",
            securityMessage: 'You have no access to the action'
        ),
        new Patch(
            denormalizationContext: ['groups' => ['user:edit']],
            security: "is_granted('ROLE_ADMIN') or object == user",
            securityMessage: 'You have no access to the action'
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'You have no access to the action'
        ),
        new GetCollection(
            normalizationContext: ['enable_max_depth' => true, 'groups' => ['user:read']],
            security: "is_granted('ROLE_MODERATOR')",
            securityMessage: 'You have no access to the action',
            /*
            uriTemplate: '/grimoire/{id}', 
            requirements: ['id' => '\d+'], 
            defaults: ['color' => 'brown'], 
            options: ['my_option' => 'my_option_value'], 
            schemes: ['https'], 
            host: '{subdomain}.api-platform.com'
            controller: GetWeather::class
            status: 301
            */            
        ),
        new Post(
            denormalizationContext: ['groups' => ['user:write']],
        ),
    ],
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['username', 'email'], message: 'This value is already used')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_SUPERADMIN = "ROLE_SUPERADMIN";
    public const ROLE_ADMIN = "ROLE_ADMIN";
    public const ROLE_USER = "ROLE_USER";
    public const ROLE_MODERATOR = "ROLE_MODERATOR";
    public const ROLE_AUTHOR = "ROLE_AUTHOR";
    public const ROLE_EDITOR = "ROLE_EDITOR";
    public const ROLE_DEFAULT = self::ROLE_USER;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read', 'comment:read', 'post:read'])]
    #[ApiProperty(identifier: true)]
    private ?int $id = null;

    /**
     * User random UUID
     *
     * @var string|null
     */
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[Groups(['user:read', 'comment:read', 'post:read'])]
    private ?Uuid $uuid = null;    

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:read', 'user:write'])]
    #[Assert\NotBlank(groups: ['admin:write'])]
    #[Assert\Length(min: 3, max: 180)]
    private ?string $username = null;

    #[ORM\Column]
    #[Groups(['admin:read', 'admin:write'])]
    private array $roles = [self::ROLE_DEFAULT];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\Length(max: 255)]
    private ?string $password = null;

    #[Groups(['user:write', 'user:edit'])]
    #[SerializedName('password')]
    private ?string $plainPassword = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read', 'user:write', 'user:edit'])]
    #[Assert\Length(min: 3, max: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 80)]
    #[Groups(['user:write', 'user:edit', 'admin:read', 'admin:write', 'user:extended:read'])]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(min: 5, max: 80)]
    private ?string $email = null;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: BlogPost::class, orphanRemoval: true)]
    #[Groups(['user:read'])]
    #[MaxDepth(1)]
    #[ApiProperty(readableLink: false, writableLink: false)]  
    private Collection $posts;

    #[Groups(['user:read'])]  
    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Comment::class, orphanRemoval: true)]
    #[MaxDepth(1)]
    #[ApiProperty(readableLink: false, writableLink: false)]    
    private Collection $comments;

    #[ORM\Column]
    private ?bool $isEnabled = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $confirmationToken = null;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     * @return self
     */
    public function setUuid(Uuid $uuid): self 
    {
        $this->uuid = $uuid;
        
        return $this;
    }     

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the value of plainPassword
     */ 
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Set the value of plainPassword
     *
     * @return  self
     */ 
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }       

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getIsEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): static
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }   
    
    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): static
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }    

    /**
     * @return Collection<int, BlogPost>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(BlogPost $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setAuthor($this);
        }

        return $this;
    }

    public function removePost(BlogPost $post): static
    {
        $this->posts->removeElement($post);

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
            $comment->setAuthor($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    // public static function createFromPayload($username, array $payload): JWTUserInterface
    // {
    //     return new self(
    //         $username,
    //         $payload['uuid']
    //     );
    // }

}
