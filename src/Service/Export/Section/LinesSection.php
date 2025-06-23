<?php

namespace App\Service\Export\Section;

use App\Entity\WorkMonth;
use App\Service\Export\StyleProvider;
use App\Service\WorkDurationFormatter;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

readonly class LinesSection implements TimeSheetSectionInterface
{
    public function __construct(
        private StyleProvider $styleProvider,
        private WorkDurationFormatter $formatter,
    ) {}

    public function apply(Worksheet $sheet, WorkMonth $workMonth, SheetContext $context): void
    {
        $style = $this->styleProvider->getDefaultStyle();

        foreach ($workMonth->getWorkDays() as $workDay) {
            foreach ($workDay->getWorkPeriods() as $period) {
                $sheet->fromArray([
                    $workDay->getDate()->format('d/m/Y'),
                    $period->getTimeStart()->format('H:i'),
                    $period->getTimeEnd()->format('H:i'),
                    $this->formatter->format($period->getDuration()),
                    '',
                    $period->getLocation(),
                    '',
                    $period->getReplacedAgent()
                ], null, 'A' . $context->line);

                $sheet->mergeCells("D{$context->line}:E{$context->line}");
                $sheet->mergeCells("F{$context->line}:G{$context->line}");
                $sheet->mergeCells("H{$context->line}:N{$context->line}");

                $sheet->getStyle("A{$context->line}:N{$context->line}")->applyFromArray($style);
                $sheet->getRowDimension($context->line)->setRowHeight(15);

                $context->advance();
            }
        }
    }
}
