<?php

namespace App\EventListener;

use Doctrine\ORM\Events;
use App\Entity\AuthoredEntityInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsDoctrineListener(event: Events::prePersist, priority: 500, connection: 'default')]
class AuthoredEntityListener
{
    private $tokenStorage;
    
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof AuthoredEntityInterface || null !== $entity->getAuthor()) {
            return;
        }

        $user = $this->tokenStorage->getToken()->getUser();

        $entity->setAuthor($user);
    }
}