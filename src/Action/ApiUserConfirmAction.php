<?php

namespace App\Action;

use App\Entity\UserConfirmation;
use App\Service\UserConfirmationService;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsController]
class ApiUserConfirmAction extends AbstractController
{
    private $userConfirmationService;
    
    public function __construct(UserConfirmationService $userConfirmationService) {
        $this->userConfirmationService = $userConfirmationService;
    }

    public function __invoke(UserConfirmation $userConfirmation): UserConfirmation
    {
        $user = $this->userConfirmationService->confirmUserAccount($userConfirmation->getConfirmationToken());

        return $userConfirmation;
    }
}

