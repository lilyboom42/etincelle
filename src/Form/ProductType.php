<?php

namespace App\Form;

use App\Form\ProductImageType;
use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix',
                'currency' => 'EUR',
            ])
            ->add('stockQuantity', IntegerType::class, [
                'label' => 'Quantité en stock',
            ])
            ->add('productImages', CollectionType::class, [
                'entry_type' => ProductImageType::class, // Spécifie le type du champ pour les images
                'allow_add' => true, // Autoriser l'ajout de nouvelles images
                'allow_delete' => true, // Autoriser la suppression
                'by_reference' => false,
                'prototype' => true, // Permet d'ajouter dynamiquement des images
                // 'label' => 'Images du produit',
                'label' => false, // Désactive le label pour le champ collection
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
