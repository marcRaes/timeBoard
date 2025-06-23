<?php

namespace App\Service\Export\Section;

use App\Entity\WorkMonth;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

interface TimeSheetSectionInterface
{
    public function apply(Worksheet $sheet, WorkMonth $workMonth, SheetContext $context): void;
}
