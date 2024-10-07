<?php

// src/Form/BlogType.php

namespace App\Form;

use App\Entity\BlogPost;
use App\Form\MediaType; // Formulaire pour gérer les médias
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('content')
            ->add('media', CollectionType::class, [
                'entry_type' => MediaType::class, // Utiliser un formulaire spécifique pour Media
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false, // Nécessaire pour les relations OneToMany
                'prototype' => true, // Permet de gérer dynamiquement les médias
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BlogPost::class,
        ]);
    }
}
