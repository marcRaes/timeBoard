<?php

namespace App\Service\Export;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

interface SpreadsheetWriterInterface
{
    public function write(Spreadsheet $spreadsheet, string $path): void;
}
