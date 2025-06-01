<?php

namespace App\Form;

use App\Entity\WorkPeriod;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkPeriodTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('timeStart', TimeType::class, [
                'label' => 'Heure début',
                'input' => 'datetime',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('timeEnd', TimeType::class, [
                'label' => 'Heure fin',
                'input' => 'datetime',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('durationDisplay', TextType::class, [
                'label' => 'Temps de travail',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'readonly' => true,
                ],
            ])
            ->add('duration', HiddenType::class)
            ->add('location', TextType::class, [
                'label' => 'Lieu de travail',
                'required' => false,
            ])
            ->add('replacedAgent', TextType::class, [
                'label' => 'Agent remplacé',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WorkPeriod::class,
        ]);
    }
}
