<?php

namespace App\Form;

use App\Entity\WorkPeriod;
use App\Form\EventListener\WorkPeriodDurationDisplayListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Enum\WorkPeriodType as WorkPeriodEnum;

class WorkPeriodType extends AbstractType
{
    public function __construct(
        private readonly WorkPeriodDurationDisplayListener $listener
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('timeStart', TimeType::class, [
                'label' => 'Heure début',
                'input' => 'datetime',
                'widget' => 'single_text',
            ])
            ->add('timeEnd', TimeType::class, [
                'label' => 'Heure fin',
                'input' => 'datetime',
                'widget' => 'single_text',
            ])
            ->add('durationDisplay', TextType::class, [
                'label' => 'Durée',
                'mapped' => false,
                'disabled' => true,
                'attr' => [
                    'readonly' => true,
                ],
            ])
            ->add('duration', HiddenType::class)
            ->add('location', TextType::class, [
                'label' => 'Lieu de travail',
            ])
            ->add('replacedAgent', TextType::class, [
                'label' => 'Agent remplacé',
                'required' => false,
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de créneau',
                'choices' => [
                    '🛠️ Travail' => WorkPeriodEnum::Work,
                    '🎓 Réunion / Formation' => WorkPeriodEnum::MeetingTraining,
                ],
                'data' => WorkPeriodEnum::Work,
                'required' => true,
                'placeholder' => false,
                'choice_translation_domain' => false,
                'choice_value' => fn (?WorkPeriodEnum $enum) => $enum?->value,
            ])
            ->addEventSubscriber($this->listener);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WorkPeriod::class,
        ]);
    }
}
