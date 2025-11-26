<?php

namespace App\Service\Demo;

use App\DTO\DemoWorkDayDTO;
use App\Entity\User;
use App\Entity\WorkDay;
use App\Entity\WorkMonth;
use App\Entity\WorkPeriod;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;

class DemoWorkDayGenerator
{
    private array $monthsCache;

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        $this->monthsCache = [];
    }

    /**
     * @throws RandomException
     */
    public function createDay(User $user, DemoWorkDayDTO $dto): WorkDay
    {
        $date = $dto->date;
        $key = $user->getId() . '-' . $date->format('Y-m');

        if (!isset($this->monthsCache[$key])) {
            $month = (int)$date->format('m');
            $year  = (int)$date->format('Y');

            $workMonth = new WorkMonth();
            $workMonth->setUser($user);
            $workMonth->setMonth($month);
            $workMonth->setYear($year);

            $this->entityManager->persist($workMonth);

            $this->monthsCache[$key] = $workMonth;
        }

        $workMonth = $this->monthsCache[$key];

        $workDay = new WorkDay();
        $workDay->setWorkMonth($workMonth);
        $workDay->setDate($date);
        $workDay->setHasLunchTicket($dto->lunchTicket);

        $this->entityManager->persist($workDay);

        $remaining = $dto->maxDailyMinutes;
        $start = new \DateTimeImmutable('09:00');

        for ($i = 0; $i < $dto->slotCount; $i++) {
            $duration = random_int(60, min(180, $remaining));

            $end = $start->modify("+{$duration} minutes");

            $workPeriod = new WorkPeriod();
            $workPeriod->setWorkDay($workDay);
            $workPeriod->setTimeStart(\DateTimeImmutable::createFromFormat('H:i', $start->format('H:i')));
            $workPeriod->setTimeEnd(\DateTimeImmutable::createFromFormat('H:i', $end->format('H:i')));
            $workPeriod->setDuration($duration);
            $workPeriod->setLocation($dto->location);
            $workPeriod->setReplacedAgent($dto->replacedAgent);

            $this->entityManager->persist($workPeriod);

            $remaining -= $duration;
            if ($remaining <= 0) {
                break;
            }
            $start = $end->modify('+15 minutes');
        }

        return $workDay;
    }
}
