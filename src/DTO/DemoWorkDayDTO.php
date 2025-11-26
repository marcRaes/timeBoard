<?php

namespace App\DTO;

use App\Enum\WorkPeriodType;

class DemoWorkDayDTO
{
    public function __construct(
        public readonly \DateTimeImmutable $date,
        public readonly string $location,
        public readonly ?string $replacedAgent,
        public readonly bool $lunchTicket,
        public readonly WorkPeriodType $type,
        public readonly int $slotCount,
        public readonly int $maxDailyMinutes = 420, // 7h
    ) {}
}
