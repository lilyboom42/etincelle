<?php

// src/Form/AppointmentType.php

namespace App\Form;

use App\Entity\Appointment;
use App\Entity\Service;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class AppointmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('datetime', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date et heure',
            ])
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => function (Service $service) {
                    return $service->getName() . ' - ' . $service->getPrice() . ' €';
                },
                'label' => 'Sélectionnez un service',
                'placeholder' => 'Choisissez un service',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
        ]);
    }
}
