<?php

namespace App\Service\TimeTracking;

use App\Entity\WorkDay;
use Doctrine\ORM\EntityManagerInterface;

final readonly class WorkDayDeleter
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function delete(WorkDay $workDay): void
    {
        $workMonth = $workDay->getWorkMonth();
        $this->entityManager->remove($workDay);

        if ($workMonth && $workMonth->getWorkDays()->count() === 1) {
            $this->entityManager->remove($workMonth);
        }

        $this->entityManager->flush();
    }
}
