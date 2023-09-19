<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        // dump($user);
        // die();
        
        if (!$user instanceof User) {
            return;
        }



        if (!$user->getIsEnabled()) {
            throw new CustomUserMessageAccountStatusException('Your user account is not activated.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }
        //if ($user->isExpired()) {
        //    throw new AccountExpiredException('...');
        //}
    }
}