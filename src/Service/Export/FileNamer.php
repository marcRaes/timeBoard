<?php

namespace App\Service\Export;

use App\Entity\WorkMonth;
use App\Service\MonthNameHelper;

readonly class FileNamer
{
    public function __construct(private MonthNameHelper $monthNameHelper) {}

    public function generate(WorkMonth $workMonth): string
    {
        return sprintf(
            'fiche_heure_%s_%s_%s_%s.pdf',
            strtolower($workMonth->getUser()->getLastName()),
            strtolower($workMonth->getUser()->getFirstName()),
            strtolower($this->monthNameHelper->getLocalizedMonthName($workMonth->getMonth())),
            $workMonth->getYear()
        );
    }
}
