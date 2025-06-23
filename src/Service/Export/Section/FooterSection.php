<?php

namespace App\Service\Export\Section;

use App\Config\TimeSheetConfig;
use App\Entity\WorkMonth;
use App\Service\Export\ImageInserter;
use App\Service\Export\StyleProvider;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

readonly class FooterSection implements TimeSheetSectionInterface
{
    public function __construct(
        private StyleProvider $styleProvider,
        private ImageInserter $imageInserter,
        private TimeSheetConfig $timeSheetConfig
    ) {}

    public function apply(Worksheet $sheet, WorkMonth $workMonth, SheetContext $context): void
    {
        $boldCentered = $this->styleProvider->getDefaultStyle(true);

        $sheet->setCellValue("C{$context->line}", "TOTAL DES HEURES");
        $sheet->setCellValue("D{$context->line}", $workMonth->getFormattedTotalTime());
        $sheet->mergeCells("D{$context->line}:E{$context->line}");
        $sheet->getStyle("C{$context->line}:E{$context->line}")->applyFromArray($boldCentered);
        $sheet->getRowDimension($context->line)->setRowHeight(15);

        $context->advance(2);
        $label = $workMonth->getLunchTickets() . ' Ticket' . ($workMonth->getLunchTickets() > 1 ? 's' : '') . ' restaurant' . ($workMonth->getLunchTickets() > 1 ? 's' : '');
        $sheet->setCellValue("H{$context->line}", $label);
        $sheet->mergeCells("H{$context->line}:J{$context->line}");
        unset($boldCentered['borders']);
        $sheet->getStyle("H{$context->line}:J{$context->line}")->applyFromArray($boldCentered);
        $sheet->getRowDimension($context->line)->setRowHeight(15);

        $this->imageInserter->insert($sheet, $this->timeSheetConfig->signatureFilename, "D" . ($context->line + 2), 70);
    }
}
