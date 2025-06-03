<?php

namespace App\Service;

class WorkDurationFormatter
{
    public function format(?int $duration): string
    {
        if ($duration === null || $duration <= 0) {
            return '-';
        }

        $hours = floor($duration / 60);
        $minutes = $duration % 60;

        return sprintf('%dH%02d', $hours, $minutes);
    }
}
