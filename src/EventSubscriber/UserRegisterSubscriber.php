<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\Uid\Uuid;
use App\Security\TokenGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Service\MailerService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserRegisterSubscriber implements EventSubscriberInterface
{  
    private $userPasswordHasher;
    private $mailer;
    private $tokenGenerator;
    
    public function __construct(
        UserPasswordHasherInterface $userPasswordHasher, 
        TokenGenerator $tokenGenerator,
        MailerService $mailer)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->tokenGenerator = $tokenGenerator;
        $this->mailer = $mailer;
    }

    public function onNewUserCreated(ViewEvent $event): void
    {
        /**
         * @var User $user
         */
        $user = $event->getControllerResult();
        
        $method = $event->getRequest()->getMethod();

        if (!$user instanceof User || !in_array($method, [Request::METHOD_POST, Request::METHOD_PATCH])) {
            return;
        }

        if ($user->getPlainPassword()) {
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getPlainPassword()));
        }    

        if (!$user->getUuid()) {
            $user->setUuid(Uuid::v4());
        }

        $user->setConfirmationToken($this->tokenGenerator->generateToken()); 

        $this->mailer->onUserRegisteredEmail($user);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onNewUserCreated', EventPriorities::PRE_WRITE]
        ];
    }
}
