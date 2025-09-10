<?php

namespace App\Dto;

use App\Entity\WorkMonth;

final readonly class WorkMonthSummaryDto
{
    public function __construct(
        public int $year,
        public int $month,
        public WorkMonth $workMonth
    ) {}
}
