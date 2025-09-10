<?php

namespace App\Service\Attachment;

use App\Entity\WorkMonth;
use App\Service\Helper\SlugHelper;
use App\Service\MonthNameHelper;

readonly class AttachmentManager
{
    public function __construct(
        private AttachmentNameGenerator $nameGenerator,
        private SlugHelper $slugHelper,
        private MonthNameHelper $monthNameHelper
    ) {}

    public function getAttachmentFileName(WorkMonth $month, string $path): string
    {
        return $this->nameGenerator->generate($month, $path);
    }

    public function getLocalizedMonthSlug(WorkMonth $month): string
    {
        return $this->slugHelper->slugify(
            $this->monthNameHelper->getLocalizedMonthName($month->getMonth())
        );
    }
}
