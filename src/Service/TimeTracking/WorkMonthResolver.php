<?php

namespace App\Service\TimeTracking;

use App\Entity\User;
use App\Entity\WorkDay;
use App\Entity\WorkMonth;
use App\Repository\WorkMonthRepository;

final class WorkMonthResolver
{
    public function __construct(private readonly WorkMonthRepository $repository) {}

    public function resolve(User $user, WorkDay $workDay): WorkMonth
    {
        $month = (int) $workDay->getDate()->format('m');
        $year = (int) $workDay->getDate()->format('Y');

        return $this->repository->findOneBy([
            'user' => $user,
            'month' => $month,
            'year' => $year,
        ]) ?? (new WorkMonth())
            ->setUser($user)
            ->setMonth($month)
            ->setYear($year);
    }
}
