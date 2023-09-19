<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserConfirmation;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Exception\AccessException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserConfirmationService
{
    private $em;
    private $userRepository;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepository)
    {
        $this->em = $em;
        $this->userRepository = $userRepository;
    }

    public function confirmUserAccount(string $confirmationToken): User
    {
        $user = $this->userRepository->findOneBy([
            'confirmationToken' => $confirmationToken
        ]);

        if ( !$user || !$user instanceof User) {
            throw new AccessException('Token is incorrect');
        }

        $user->setConfirmationToken(null);
        $user->setIsEnabled(true);

        $this->em->persist($user);
        $this->em->flush();  

        return $user;
    }
}