<?php

namespace App\Form;

use App\Entity\ProductCustomization;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for Product Customization.
 * Type de formulaire pour la personnalisation de produit.
 */
class ProductCustomizationType extends AbstractType
{
    /**
     * Builds the form for product customization.
     * Construit le formulaire pour la personnalisation de produit.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customizationName', TextType::class, [
                'label' => 'Nom de la personnalisation', // Label for the customization name field
                                                        // Libellé pour le champ du nom de la personnalisation
                'required' => true,
            ])
            ->add('customizationOption', TextareaType::class, [
                'label' => 'Option de personnalisation', // Label for the customization option field
                                                        // Libellé pour le champ de l'option de personnalisation
                'required' => true,
            ])
            ->add('product', null, [
                'choice_label' => 'name',  // Display the name property of the Product entity in the form choices
                                           // Affiche la propriété 'name' de l'entité Product dans les choix du formulaire
                'label' => 'Produit',      // Label for the product field
                                           // Libellé pour le champ produit
                'required' => true,
            ]);
    }

    /**
     * Configures the options for this form type.
     * Configure les options pour ce type de formulaire.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductCustomization::class, // The data class that this form type is bound to
                                                        // La classe de données à laquelle ce type de formulaire est lié
        ]);
    }
}
