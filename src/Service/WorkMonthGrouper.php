<?php

namespace App\Service;

use App\DTO\WorkMonthSummaryDTO;
use App\Entity\WorkMonth;

/**
 * Regroupe les WorkMonths par année et mois avec création des DTO.
 */
final class WorkMonthGrouper
{
    /**
     * @param WorkMonth[] $workMonths
     * @return array<int, array<int, WorkMonthSummaryDTO>>
     */
    public function groupByYearAndMonth(array $workMonths): array
    {
        $grouped = [];

        foreach ($workMonths as $workMonth) {
            $year = $workMonth->getYear();
            $month = $workMonth->getMonth();

            $grouped[$year][$month] = new WorkMonthSummaryDTO(
                $year,
                $month,
                $workMonth
            );
        }

        // On trie les mois dans chaque année par ordre décroissant
        foreach ($grouped as &$months) {
            krsort($months);
        }

        // On trie les années par ordre décroissant
        krsort($grouped);

        return $grouped;
    }
}
