<?php

namespace App\Form;

use App\Entity\UserDetails;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserDetailsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('city')
            ->add('address')
            ->add('country')
            ->add('postalCode')
            ->add('phoneNumber')
            ->add('birthDate', DateType::class, [
                'widget' => 'single_text',  // Utilise un input de type date
                'format' => 'yyyy-MM-dd',   // Spécifie le format de la date
                'html5' => true,            // Active le support HTML5 pour le champ de type date
                'label' => 'Date de naissance',
                'attr' => ['autocomplete' => 'bday'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserDetails::class,
        ]);
    }
}
