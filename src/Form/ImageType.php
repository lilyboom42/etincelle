<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\ProductImage;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;


class ImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('imageFile', VichImageType::class, [
            'required' => false,
            'label' => 'Télécharger une image',
            'allow_delete' => false,
            'download_uri' => false,
        ])
        ->add('delete', CheckboxType::class, [
            'label' => 'Supprimer l\'image actuelle',
            'required' => false,
            'mapped' => false,  // Ce champ n'est pas mappé à l'entité
        ]);
}
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductImage::class,
        ]);
    }
}
