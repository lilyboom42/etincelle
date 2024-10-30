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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\CategoryProduct;

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
                'entry_type' => ImageType::class,  
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,  
                'prototype_name' => '__name__',  
                'attr' => [
                    'class' => 'product-images-collection',
                ],
            ])
            ->add('categoryProduct', EntityType::class, [
                'class' => CategoryProduct::class,
                'choice_label' => 'name', 
                'label' => 'Catégorie',
                'placeholder' => 'Sélectionnez une catégorie',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}