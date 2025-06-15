<?php

namespace App\Form;

use App\Entity\WorkReportSubmission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
                'label' => 'Frais de transport (Optionnel)',
                'required' => false,
                'constraints' => [
                    new Assert\File([
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Le justificatif de transport ne doit pas dépasser 2 Mo.',
                        'mimeTypes' => ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Le format du justificatif de transport n\'est pas supporté. Veuillez joindre un PDF, JPEG, PNG ou GIF.',
                    ]),
                ],
                'attr' => [
                    'accept' => '.pdf,image/*',
                    'data-max-size' => 2097152,
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer',
                'attr' => ['class' => 'btn btn-primary'],
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
