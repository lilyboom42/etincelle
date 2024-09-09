<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\ProductImage;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ImageType extends AbstractType
{
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                ->add('imageFile', VichImageType::class, [
                    'required' => false,
                    'label' => 'Télécharger une image',
                    'allow_delete' => false, // Permet de supprimer l'image existante
                    'download_uri' => false, // Permet de télécharger l'image actuelle
                ])
                
            ;
        }
    
        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
             'data_class' => ProductImage::class,
            ]);
        }
    }
    
