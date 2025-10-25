<?php

namespace App\Service\Attachment;

use App\DTO\TransportProofDTO;
use App\Entity\WorkMonth;
use App\Exception\InvalidAttachmentException;
use App\Service\Formatter\MonthNameFormatter;
use App\Service\Formatter\SlugGenerator;

readonly class TransportProofAttachmentHandler
{
    public function __construct(
        private AttachmentValidator $validator,
        private SlugGenerator $slugGenerator,
        private MonthNameFormatter $monthNameFormatter,
    )
    {}

    public function prepare(WorkMonth $workMonth, ?string $transportProof): TransportProofDTO|null
    {
        if ($transportProof) {
            if (!is_file($transportProof)) {
                throw new InvalidAttachmentException('Fichier justificatif introuvable.');
            }

            $validationResult = $this->validator->validate(
                $transportProof,
                ['pdf', 'jpg', 'png', 'gif'],
                ['application/pdf', 'image/jpeg', 'image/png', 'image/gif']
            );
            $extension = $validationResult['extension'];
            $mimeType = $validationResult['mimeType'];

            $monthNameSlug = $this->slugGenerator->slugify(
                $this->monthNameFormatter->getLocalizedMonthName($workMonth->getMonth())
            );

            return new TransportProofDTO(
                $transportProof,
                sprintf('justificatif_transport_%s_%d.%s', $monthNameSlug, $workMonth->getYear(), $extension),
                $mimeType
            );
        }

        return null;
    }
}
