<?php

namespace App\Form;

use App\Entity\Image;
use App\Entity\BlogPost;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('imageFile', FileType::class)
            ->add('blogPost', EntityType::class, [
                'class' => BlogPost::class,
                'choice_label' => 'Post',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Image::class]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return '';
    }

}
