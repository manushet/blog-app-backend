<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserConfirmationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    private $userConfirmationService;

    public function __construct(UserConfirmationService $userConfirmationService) {
        $this->userConfirmationService = $userConfirmationService;
    }

    public function confirm(string $confirmationToken): Response
    {
        /**
         * @var User $user
         */
        $user = $this->userConfirmationService->confirmUserAccount($confirmationToken);

        return $this->render('account.activated.html.twig', [
            'name' => $user->getName(),
        ]);
    }
}
