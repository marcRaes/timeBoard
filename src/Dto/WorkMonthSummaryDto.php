<?php

namespace App\Dto;

use App\Entity\WorkMonth;

final class WorkMonthSummaryDto
{
    public function __construct(
        public readonly int $year,
        public readonly int $month,
        public readonly WorkMonth $workMonth
    ) {}
}
