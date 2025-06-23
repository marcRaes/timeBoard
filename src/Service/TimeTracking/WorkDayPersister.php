<?php

namespace App\Service\TimeTracking;

use App\Entity\WorkDay;
use App\Entity\WorkMonth;
use Doctrine\ORM\EntityManagerInterface;

final class WorkDayPersister
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function save(WorkDay $workDay, WorkMonth $workMonth): void
    {
        $workDay->setWorkMonth($workMonth);

        $this->em->persist($workMonth);
        $this->em->persist($workDay);
        $this->em->flush();
    }
}
