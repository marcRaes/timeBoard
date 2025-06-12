<?php

namespace App\Service;

use App\Dto\WorkMonthSummaryDto;
use App\Entity\WorkMonth;

/**
 * Regroupe les WorkMonths par année et mois avec création des DTO.
 */
final class WorkMonthGrouper
{
    /**
     * @param WorkMonth[] $workMonths
     * @return array<int, array<int, WorkMonthSummaryDto>>
     */
    public function groupByYearAndMonth(array $workMonths): array
    {
        $grouped = [];

        foreach ($workMonths as $workMonth) {
            $year = $workMonth->getYear();
            $month = $workMonth->getMonth();

            $grouped[$year][$month] = new WorkMonthSummaryDto(
                $year,
                $month,
                $workMonth
            );
        }

        // On trie les années par ordre décroissant
        krsort($grouped);

        return $grouped;
    }
}