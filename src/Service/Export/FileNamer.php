<?php

namespace App\Service\Export;

use App\Entity\WorkMonth;
use App\Service\Helper\SlugHelper;
use App\Service\MonthNameHelper;

readonly class FileNamer
{
    public function __construct(
        private MonthNameHelper $monthNameHelper,
        private SlugHelper $slugHelper,
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
