<?php

namespace App\Service\Attachment;

use App\Entity\WorkMonth;
use App\Exception\InvalidAttachmentException;
use App\Service\Helper\SlugHelper;
use App\Service\MonthNameHelper;

readonly class AttachmentNameGenerator
{
    public function __construct(
        private MonthNameHelper $monthNameHelper,
        private SlugHelper $slugHelper,
        private AttachmentValidator $attachmentValidator
    ) {}

    public function generate(WorkMonth $month, string $path): string
    {
        if (!is_file($path)) {
            throw new InvalidAttachmentException('Fichier justificatif introuvable.');
        }

        $extension = $this->attachmentValidator->validate($path);
        $monthNameSlug = $this->slugHelper->slugify(
            $this->monthNameHelper->getLocalizedMonthName($month->getMonth())
        );

        return sprintf('justificatif_transport_%s_%d.%s', $monthNameSlug, $month->getYear(), $extension);
    }
}
