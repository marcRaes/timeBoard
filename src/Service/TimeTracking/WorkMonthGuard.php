<?php

namespace App\Service\TimeTracking;

use App\Entity\WorkMonth;
use App\Exception\WorkMonthAlreadySentException;
use App\Service\MonthNameHelper;

final class WorkMonthGuard
{
    public function __construct(private readonly MonthNameHelper $monthNameHelper) {}

    public function ensureNotSent(WorkMonth $workMonth): void
    {
        if ($workMonth->isSent()) {
            throw new WorkMonthAlreadySentException(sprintf(
                'Impossible, vous avez déjà envoyé le rapport pour le mois de %s %d.',
                $this->monthNameHelper->getLocalizedMonthName($workMonth->getMonth()),
                $workMonth->getYear()
            ));
        }
    }
}
