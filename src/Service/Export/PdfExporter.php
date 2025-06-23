<?php

namespace App\Service\Export;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;

class PdfExporter implements SpreadsheetWriterInterface
{
    public function write(Spreadsheet $spreadsheet, string $path): void
    {
        $writer = new Mpdf($spreadsheet);
        $writer->save($path);
    }
}
