<?php

namespace App\Service\Export\Section;

use App\Service\Export\ImageInserter;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\Filesystem\Filesystem;

readonly class SignatureSection implements TimeSheetSectionInterface
{
    public function __construct(
        private ImageInserter $imageInserter,
        private SheetContext $sheetContext,
    )
    {}

    public function apply(Worksheet $sheet, $workMonth, SheetContext $context): void
    {
        if (file_exists($this->sheetContext->getSignatureData()) && filesize($this->sheetContext->getSignatureData()) > 0) {
            $this->imageInserter->insert(
                $sheet,
                $this->sheetContext->getSignatureData(),
                "D" . $context->line,
                70
            );
        } else {
            $sheet->setCellValue("D{$context->line}", '[Prévisualisation sans signature - version non officielle]');
            $sheet->mergeCells("D{$context->line}:J{$context->line}");

            $sheet->getStyle("D{$context->line}:J{$context->line}")->applyFromArray([
                'font' => [
                    'italic' => true,
                    'color' => ['rgb' => '555555'],
                    'size' => 12,
                ],
                'alignment' => [
                    'horizontal' => 'right',
                ],
            ]);
        }
    }
}
