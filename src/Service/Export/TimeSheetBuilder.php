<?php

namespace App\Service\Export;

use App\Config\TimeSheetConfig;
use App\Entity\WorkMonth;
use App\Service\Export\Section\SheetContext;
use App\Service\Export\Section\TimeSheetSectionInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

readonly class TimeSheetBuilder
{
    /**
     * @param iterable<TimeSheetSectionInterface> $sections
     */
    public function __construct(
        private iterable $sections,
        private TimeSheetConfig $timeSheetConfig,
        private SheetContext $sheetContext
    ) {}

    public function build(WorkMonth $workMonth): Spreadsheet
    {
        $spreadsheet = IOFactory::load($this->timeSheetConfig->templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($this->sections as $section) {
            $section->apply($sheet, $workMonth, $this->sheetContext);
        }

        return $spreadsheet;
    }
}
