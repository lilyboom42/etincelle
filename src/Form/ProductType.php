<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\User; // Importez correctement la classe User depuis l'espace de noms App\Entity
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
            ]);
            // ->add('favoritedBy', EntityType::class, [
            //     'class' => User::class, // Utilisez l'espace de noms correct ici
            //     'choice_label' => 'id',
            //     'multiple' => true,
            //     'label' => 'Favori par les utilisateurs'
            // ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
