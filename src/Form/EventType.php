<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use App\Entity\Event;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('eventDate', null, [
                'widget' => 'single_text',
            ])
            ->add('mediaFiles', FileType::class, [ // Renommé de 'media' à 'mediaFiles'
                'label' => 'Ajouter des médias (JPEG, PNG, MP4)',
                'mapped' => false,
                'required' => false,
                'multiple' => true, // Permet l'upload multiple
                'constraints' => [
                    new All([
                        'constraints' => [
                            new File([
                                'maxSize' => '30M',
                                'mimeTypes' => [
                                    'image/jpeg',
                                    'image/png',
                                    'video/mp4',
                                ],
                                'mimeTypesMessage' => 'Veuillez télécharger un fichier valide (JPEG, PNG, MP4).',
                            ])
                        ],
                    ]),
                ],
                'attr' => [
                    'accept' => 'image/jpeg, image/png, video/mp4',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
