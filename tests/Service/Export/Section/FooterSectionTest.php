<?php

namespace App\Tests\Service\Export\Section;

use App\Config\TimeSheetConfig;
use App\Entity\WorkMonth;
use App\Service\Export\Section\FooterSection;
use App\Service\Export\Section\SheetContext;
use App\Service\Export\StyleProvider;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\RowDimension;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FooterSectionTest extends TestCase
{
    #[DataProvider('provideLunchTicketCases')]
    public function testApplySetsFooterCorrectly(int $tickets): void
    {
        $styleWithBorders = [
            'font' => ['bold' => true],
            'borders' => ['bottom' => ['style' => 'thin']],
        ];

        $styleWithoutBorders = [
            'font' => ['bold' => true],
        ];

        $styleProvider = $this->createMock(StyleProvider::class);
        $styleProvider
            ->method('getDefaultStyle')
            ->with(true)
            ->willReturn($styleWithBorders);

        // ✅ On n’a plus besoin d’un ImageInserter ni d’une signature
        $config = new TimeSheetConfig('/template.xlsx', '/pdf', '/img', 'logo.png');

        $workMonth = $this->createMock(WorkMonth::class);
        $workMonth->method('getFormattedTotalTime')->willReturn('123h');
        $workMonth->method('getLunchTickets')->willReturn($tickets);

        // On capture les styles appliqués
        $capturedStyles = [];

        $styleMock = $this->createMock(Style::class);
        $styleMock
            ->method('applyFromArray')
            ->willReturnCallback(function (array $style) use (&$capturedStyles, $styleMock) {
                $capturedStyles[] = $style;
                return $styleMock;
            });

        $rowDimensionMock = $this->createMock(RowDimension::class);
        $rowDimensionMock->expects($this->exactly(2))
            ->method('setRowHeight')
            ->with(15)
            ->willReturnSelf();

        $sheet = $this->createMock(Worksheet::class);
        $sheet->expects($this->exactly(3))->method('setCellValue');
        $sheet->expects($this->exactly(2))->method('mergeCells');
        $sheet->expects($this->exactly(2))->method('getStyle')->willReturn($styleMock);
        $sheet->expects($this->exactly(2))->method('getRowDimension')->willReturn($rowDimensionMock);

        $context = new SheetContext();
        $context->line = 11;

        $section = new FooterSection($styleProvider);
        $section->apply($sheet, $workMonth, $context);

        // ✅ Vérifie la progression du contexte et l’application des styles
        $this->assertEquals(15, $context->line);
        $this->assertCount(2, $capturedStyles);
        $this->assertSame($styleWithBorders, $capturedStyles[0]);

        // Après le unset, la bordure doit disparaître
        $expectedWithoutBorders = $styleWithBorders;
        unset($expectedWithoutBorders['borders']);
        $this->assertSame($expectedWithoutBorders, $capturedStyles[1]);
    }

    public static function provideLunchTicketCases(): array
    {
        return [
            'no ticket' => [0],
            'one ticket' => [1],
            'multiple tickets' => [3],
        ];
    }
}
