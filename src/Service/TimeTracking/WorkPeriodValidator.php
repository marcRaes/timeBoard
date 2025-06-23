<?php

namespace App\Service\TimeTracking;

use App\Entity\WorkDay;
use App\Entity\WorkPeriod;

final class WorkPeriodValidator
{
    public function isValid(WorkPeriod $period): bool
    {
        return $period->getTimeStart()
            && $period->getTimeEnd()
            && $period->getDuration()
            && $period->getLocation();
    }

    public function removeInvalidPeriods(WorkDay $workDay): void
    {
        foreach ($workDay->getWorkPeriods()->toArray() as $period) {
            if (!$this->isValid($period)) {
                $workDay->removeWorkPeriod($period);
            }
        }
    }
}
