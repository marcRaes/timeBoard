<?php

namespace App\Form;

use App\Entity\WorkDay;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;

class WorkDayType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
            ])
            ->add('hasLunchTicket', CheckboxType::class, [
                'label' => 'Ticket restaurant',
                'required' => false,
            ])
            ->add('workPeriods', CollectionType::class, [
                'entry_type' => WorkPeriodType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__name__',
                'constraints' => [
                    new Count([
                        'max' => 4,
                        'maxMessage' => 'Vous ne pouvez pas ajouter plus de {{ limit }} créneaux horaires.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WorkDay::class,
        ]);
    }
}
