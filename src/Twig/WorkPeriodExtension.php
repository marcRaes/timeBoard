<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class WorkPeriodExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('calculate_work_duration', [$this, 'calculateWorkDuration']),
        ];
    }

    public function calculateWorkDuration(?int $duration): string
    {
        if ($duration === null || $duration <= 0) {
            return '-';
        }

        $hours = floor($duration / 60);
        $minutes = $duration % 60;

        return sprintf('%dH%02d', $hours, $minutes);
    }
}
