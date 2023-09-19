<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Doctrine\ORM\Mapping as ORM;
use App\Model\TimestampableTrait;
use App\Repository\ImageRepository;
use App\Action\ApiImageUploadAction;
use ApiPlatform\Metadata\ApiResource;
use App\Model\TimestampableInterface;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
#[Vich\Uploadable]
#[ApiResource(   
    shortName: 'post_images',
    normalizationContext: ['enable_max_depth' => true, 'groups' => ['post_image:read']],
    denormalizationContext: ['groups' => ['post_image:write', 'post_image:edit']],
    operations: [
        new Post(
            uriTemplate: '/post_images', 
            controller: ApiImageUploadAction::class,
            defaults: ['_api_receive' => false],
            denormalizationContext: ['groups' => ['post_image:write']],
        ),
        new Get(
            normalizationContext: ['enable_max_depth' => true, 'groups' => ['post_image:read']],
        ),
        new GetCollection(
            normalizationContext: ['enable_max_depth' => true, 'groups' => ['post_image:read']],
        ),
        new Delete(),
        new Patch(
            denormalizationContext: ['groups' => ['post_image:edit']],
        )
    ],
)]
#[ApiFilter(OrderFilter::class, properties: ['updatedAt' => 'DESC'])]
#[ORM\HasLifecycleCallbacks]
class Image implements TimestampableInterface
{
    use TimestampableTrait;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['post_image:read', 'post:read'])]
    private ?int $id = null;

    #[Vich\UploadableField(mapping: 'blog', fileNameProperty: 'imageName', size: 'imageSize', mimeType: 'imageType')]
    #[Assert\NotNull]
    #[Groups(['post_image:write', 'post_image:edit'])]
    private File|FileBag|UploadedFile|null $imageFile = null;

    #[ORM\Column(nullable: true, length: 255)]
    #[Groups(['post_image:read', 'post:read'])]
    private ?string $imageUrl = null;

    #[ORM\Column(nullable: true, length: 255)]
    #[Groups(['post_image:read'])]
    private ?string $imageName = null;  
    
    #[ORM\Column(nullable: true, length: 255)]
    #[Groups(['post_image:read'])]
    private ?string $imageType = null;   

    #[ORM\Column(nullable: true)]
    #[Groups(['post_image:read'])]
    private ?int $imageSize = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['post_image:read', 'post_image:write'])]
    #[MaxDepth(1)]
    private ?BlogPost $blogPost = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param File|FileBag|UploadedFile|null $imageFile
     */
    public function setImageFile(File|FileBag|UploadedFile|null $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            $this->imageType = $imageFile->getMimeType();
            $this->imageSize = $imageFile->getSize();
            $this->imageUrl = $_SERVER['POST_IMAGES_PATH'] . '/' . $this->imageName;
        }
    }

    public function getImageFile(): File|FileBag|UploadedFile|null
    {
        return $this->imageFile;
    }    

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function setImageSize(?int $imageSize): void
    {
        $this->imageSize = $imageSize;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    /**
     * Get the value of imageType
     */ 
    public function getImageType()
    {
        return $this->imageType;
    }

    /**
     * Set the value of imageType
     *
     * @return  self
     */ 
    public function setImageType($imageType)
    {
        $this->imageType = $imageType;

        return $this;
    }

    /**
     * Get the value of imageName
     */ 
    public function getImageName()
    {
        return $this->imageName;
    }

    /**
     * Set the value of imageName
     *
     * @return  self
     */ 
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;

        return $this;
    }

    public function getBlogPost(): ?BlogPost
    {
        return $this->blogPost;
    }

    public function setBlogPost(?BlogPost $blogPost): static
    {
        $this->blogPost = $blogPost;

        return $this;
    }
}
