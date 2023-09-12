<?php


namespace App\Model;


use DateTime;

interface TimestampableInterface
{
    /**
     * @param DateTime $createdAt
     * @return void
     */
    public function setCreatedAt(DateTime $createdAt);

    /**
     * @param DateTime $updatedAt
     * @return void
     */
    
    public function setUpdatedAt(DateTime $updatedAt);

    /**
     * @return DateTime
     */
    public function getUpdatedAt(): DateTime;

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime;

    /**
     * @return void
     */
    public function timestampablePrePersist();

    /**
     * @return void
     */
    public function timestampablePreUpdate();
}