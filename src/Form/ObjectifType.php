<?php

namespace App\Form;

use App\Entity\Objectif;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ObjectifType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('vo2Objectif', NumberType::class, [
                'required' => false,
                'label' => 'VO2 Objectif'
            ])
            ->add('chargeHebdoObjectif', NumberType::class, [
                'required' => false,
                'label' => 'Charge Hebdomadaire'
            ])
            ->add('masseGrasseObjectif', NumberType::class, [
                'required' => false,
                'label' => 'Masse Grasse (%)'
            ])
            ->add('performanceObjectif', TextType::class, [
                'required' => false,
                'label' => 'Performance Cible'
            ])
            ->add('competitionNom', TextType::class, [
                'required' => false,
                'label' => 'Compétition'
            ])
            ->add('competitionDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text'
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text'
            ])
            ->add('macrocycle', TextType::class)
            ->add('mesocycle', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Objectif::class,
        ]);
    }
}