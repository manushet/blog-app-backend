<?php

namespace App\Action;

use App\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use ApiPlatform\Symfony\Validator\Exception\ValidationException;
use App\Form\ImageType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsController]
class ApiImageUploadAction extends AbstractController
{   
    /**
     * @var FormFactoryInterface $formFactory
     */
    private $formFactory;

    /**
     * @var EntityManagerInterface $em
     */    
    private $em;

    /**
     * @var ValidatorInterface $validator
     */  
    private $validator;

    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $em,
        ValidatorInterface $validator) {
        $this->formFactory = $formFactory;
        $this->em = $em;
        $this->validator = $validator;
    }

    public function __invoke(Request $request)
    {
        $image = new Image();

        $form = $this->formFactory->create(ImageType::class, $image);

        $form->handleRequest($request);

        // dump($image);
        // die();

        if ($form->isSubmitted() && $form->isValid()) {
            
            $this->em->persist($image);         

            $this->em->flush();

            return $image;
        }

        throw new ValidationException($this->validator->validate($image));
    }
}

