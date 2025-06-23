<?php

namespace App\Service\Helper;

use App\Entity\WorkMonth;
use App\Exception\InvalidAttachmentException;
use App\Service\MonthNameHelper;
use Symfony\Component\HttpFoundation\File\File;

readonly class AttachmentHelper
{
    public function __construct(private MonthNameHelper $monthNameHelper) {}

    public function getAttachmentFileName(WorkMonth $month, string $path): string
    {
        if (!is_file($path)) {
            throw new InvalidAttachmentException('Fichier justificatif introuvable.');
        }

        $extension = (new File($path))->guessExtension();
        if (!$extension) {
            throw new InvalidAttachmentException('Extension de fichier invalide.');
        }

        return sprintf(
            'justificatif_transport_%s_%d.%s',
            $this->slugify($this->monthNameHelper->getLocalizedMonthName($month->getMonth())),
            $month->getYear(),
            $extension
        );
    }

    public function getLocalizedMonthSlug(WorkMonth $month): string
    {
        return $this->slugify($this->monthNameHelper->getLocalizedMonthName($month->getMonth()));
    }

    private function slugify(string $text): string
    {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        $text = preg_replace('~[^\\pL\d]+~u', '_', $text);
        return strtolower(trim($text, '_'));
    }
}
