<?php

namespace App\Service\Export\Section;

use App\Config\TimeSheetConfig;
use App\Entity\WorkMonth;
use App\Service\Export\ImageInserter;
use App\Service\MonthNameHelper;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

readonly class HeaderSection implements TimeSheetSectionInterface
{
    public function __construct(
        private ImageInserter $imageInserter,
        private TimeSheetConfig $timeSheetConfig,
        private MonthNameHelper $monthNameHelper,
    ) {}

    public function apply(Worksheet $sheet, WorkMonth $workMonth, SheetContext $context): void
    {
        $this->imageInserter->insert($sheet, $this->timeSheetConfig->logoFilename, 'A2', 70);

        $sheet->setCellValue('E4', $this->monthNameHelper->getLocalizedMonthName($workMonth->getMonth()));
        $sheet->setCellValue('H4', $workMonth->getYear());
        $sheet->setCellValue('B8', $workMonth->getUser()->getLastName());
        $sheet->setCellValue('B10', $workMonth->getUser()->getFirstName());
    }
}
