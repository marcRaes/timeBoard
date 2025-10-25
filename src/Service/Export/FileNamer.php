<?php

namespace App\Service\Export;

use App\Entity\WorkMonth;
use App\Service\Formatter\MonthNameFormatter;
use App\Service\Formatter\SlugGenerator;

readonly class FileNamer
{
    public function __construct(
        private MonthNameFormatter $monthNameHelper,
        private SlugGenerator      $slugHelper,
    )
    {}

    public function generate(WorkMonth $workMonth): string
    {
        return sprintf(
            'fiche_heure_%s_%s_%s_%s.pdf',
            $this->slugHelper->slugify($workMonth->getUser()->getLastName()),
            $this->slugHelper->slugify($workMonth->getUser()->getFirstName()),
            $this->slugHelper->slugify($this->monthNameHelper->getLocalizedMonthName($workMonth->getMonth())),
            $workMonth->getYear()
        );
    }
}
