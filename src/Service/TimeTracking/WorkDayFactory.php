<?php

namespace App\Service\TimeTracking;

use App\Entity\WorkDay;
use App\Entity\WorkPeriod;

final class WorkDayFactory
{
    public function create(): WorkDay
    {
        $workDay = new WorkDay();
        $workDay->setHasLunchTicket(false);

        $period = new WorkPeriod();
        $period->setWorkDay($workDay);
        $workDay->addWorkPeriod($period);

        return $workDay;
    }

    public function isNew(?WorkDay $workDay): bool
    {
        return !$workDay || !$workDay->getId();
    }
}
