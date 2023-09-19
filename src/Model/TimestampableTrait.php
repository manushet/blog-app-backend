<?php

namespace App\Model;

use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

trait TimestampableTrait 
{
    #[ORM\Column]
    #[Groups(['read'])]  
    /**
     * Created datetime 
     *
     * @var \DateTimeImmutable|null
     */    
    private \DateTimeImmutable|null $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['read'])]
    /**
     * Last updated datetime
     *
     * @var \DateTimeImmutable|null
     */
    private \DateTimeImmutable|null $updatedAt = null;

    /**
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeImmutable|null $createdAt
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */    
    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTimeImmutable|null $updatedAt
     */
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }       

    #[ORM\PrePersist]
    public function PrePersist(): void
    {
        if (!$this->createdAt) {
            $this->createdAt = new \DateTimeImmutable();
        }
        $this->updatedAt = clone $this->createdAt;
    }

    #[ORM\PreUpdate]
    public function PreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}