<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\WorkDay;
use App\Entity\WorkMonth;
use App\Entity\WorkPeriod;
use App\Repository\WorkMonthRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class WorkDayManager
{
    public function __construct(
        private WorkMonthRepository $workMonthRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function initializeCreate(): WorkDay
    {
        $workDay = new WorkDay();
        $workDay->setHasLunchTicket(false);

        $period = new WorkPeriod();
        $period->setWorkDay($workDay);
        $workDay->addWorkPeriod($period);

        return $workDay;
    }

    public function initializeWorkMonth(User $user, WorkDay $workDay): WorkMonth
    {
        $month = (int)$workDay->getDate()->format('m');
        $year = (int)$workDay->getDate()->format('Y');

        $workMonth = $this->workMonthRepository->findOneBy([
            'user' => $user,
            'month' => $month,
            'year' => $year,
        ]);

        if (!$workMonth) {
            $workMonth = new WorkMonth();
            $workMonth->setUser($user);
            $workMonth->setMonth($month);
            $workMonth->setYear($year);
        }

        return $workMonth;
    }

    public function cleanPeriodsInvalid(WorkDay $workDay): void
    {
        foreach ($workDay->getWorkPeriods()->toArray() as $workPeriod) {
            if (!$this->validatePeriod($workPeriod)) {
                $workDay->removeWorkPeriod($workPeriod);
            }
        }
    }

    private function validatePeriod(WorkPeriod $workPeriod): bool
    {
        return $workPeriod->getTimeStart() && $workPeriod->getTimeEnd() && $workPeriod->getDuration() && $workPeriod->getLocation();
    }

    public function save(WorkDay $workDay, WorkMonth $workMonth): void
    {
        $workDay->setWorkMonth($workMonth);
        $this->entityManager->persist($workMonth);
        $this->entityManager->persist($workDay);
        $this->entityManager->flush();
    }

    public function deleteWorkDay(WorkDay $workDay): void
    {
        $workMonth = $workDay->getWorkMonth();
        $this->entityManager->remove($workDay);

        // Si c’était la dernière journée dans ce mois, on supprime le mois aussi
        if ($workMonth->getWorkDays()->count() === 1) {
            $this->entityManager->remove($workMonth);
        }

        $this->entityManager->flush();
    }
}
