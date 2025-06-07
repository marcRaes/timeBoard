<?php

namespace App\Form;

use App\Entity\WorkReportSubmission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class WorkReportSubmissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('recipientEmail', EmailType::class, [
                'label' => 'Email du destinataire',
                'attr' => ['placeholder' => 'Entrez l\'email du destinataire'],
            ])
            ->add('attachmentPath', FileType::class, [
                'label' => 'Frais de transport (facultatif)',
                'required' => false,
                'constraints' => [
                    new Assert\Image([
                        'maxSize' => '2M',
                        'mimeTypes' => ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'],
                    ]),
                ],
            ])
            ->add('submit', HiddenType::class, [
                'mapped' => false,
                'label' => 'Envoyer',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WorkReportSubmission::class,
        ]);
    }
}
