<?php

namespace App\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

interface AuthoredEntityInterface
{
     /**
     * Set the author of an entity
     *
     * @return void
     */
    public function setAuthor(UserInterface $user): AuthoredEntityInterface;
}