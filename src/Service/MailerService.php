<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function onUserRegisteredEmail(User $user)
    {
        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->replyTo('info@bpostapp.com')
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Welcome to Blog Post App')
            ->htmlTemplate('email/user.registered.html.twig')
            ->context([
                'user' => $user,
            ]);
        $this->mailer->send($email);        
    }
}