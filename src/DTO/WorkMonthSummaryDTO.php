<?php

namespace App\DTO;

use App\Entity\WorkMonth;

final readonly class WorkMonthSummaryDTO
{
    public function __construct(
        public int $year,
        public int $month,
        public WorkMonth $workMonth
    ) {}
}
