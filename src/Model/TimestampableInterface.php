<?php


namespace App\Model;

interface TimestampableInterface
{
    /**
     * @param \DateTimeImmutable|null $createdAt
     * @return void
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt);

    /**
     * @param \DateTimeImmutable|null $updatedAt
     * @return void
     */
    
    public function setUpdatedAt(\DateTimeImmutable $updatedAt);

    /**
     * @return \DateTimeImmutable|null
     */
    public function getUpdatedAt(): \DateTimeImmutable;

    /**
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAt(): \DateTimeImmutable;

    /**
     * @return void
     */
    public function PrePersist();

    /**
     * @return void
     */
    public function PreUpdate();
}