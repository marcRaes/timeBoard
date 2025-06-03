<?php

namespace App\Twig;

use App\Service\WorkDurationFormatter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class WorkPeriodExtension extends AbstractExtension
{
    public function __construct(private readonly WorkDurationFormatter $formatter){}
    public function getFilters(): array
    {
        return [
            new TwigFilter('calculate_work_duration', [$this, 'calculateWorkDuration']),
        ];
    }

    public function calculateWorkDuration(?int $duration): string
    {
        return $this->formatter->format($duration);
    }
}
