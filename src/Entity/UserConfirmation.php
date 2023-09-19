<?php

namespace App\Entity;

use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\ApiResource;
use App\Action\ApiUserConfirmAction;

#[ApiResource(   
    description: 'Confirm user account with a confirmation token provided',  
    operations: [
        new Post(
            name: 'user_confirmation', 
            uriTemplate: '/user/confirm', 
            controller: ApiUserConfirmAction::class
        ),
    ],
)]
class UserConfirmation
{

    private $confirmationToken = null;

    /**
     * Get the value of confirmationToken
     */ 
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * Set the value of confirmationToken
     *
     * @return  self
     */ 
    public function setConfirmationToken($confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }
}