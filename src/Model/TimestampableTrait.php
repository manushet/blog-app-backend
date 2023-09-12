<?php

namespace App\Model;

use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

trait TimestampableTrait 
{
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

    /**
     * @return void
     */
    #[PrePersist]
    public function timestampablePrePersist()
    {
        if (!$this->createdAt) {
            $this->createdAt = new \DateTimeImmutable();
        }
        $this->updatedAt = clone $this->createdAt;
    }

    /**
     * @return void
     */
    #[PreUpdate]
    public function timestampablePreUpdate()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}