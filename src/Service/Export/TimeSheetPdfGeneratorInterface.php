<?php

namespace App\Service\Export;

use App\Entity\WorkMonth;

interface TimeSheetPdfGeneratorInterface
{
    public function generate(WorkMonth $workMonth): string;
}
