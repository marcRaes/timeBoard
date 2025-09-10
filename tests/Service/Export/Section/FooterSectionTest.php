<?php

namespace App\Tests\Service\Export\Section;

use App\Config\TimeSheetConfig;
use App\Entity\WorkMonth;
use App\Service\Export\ImageInserter;
use App\Service\Export\Section\FooterSection;
use App\Service\Export\Section\SheetContext;
use App\Service\Export\StyleProvider;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\RowDimension;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class FooterSectionTest extends TestCase
{
    /**
     * @throws Exception
     */
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
        $styleProvider->method('getDefaultStyle')->with(true)->willReturn($styleWithBorders);

        $imageInserter = $this->createMock(ImageInserter::class);
        $imageInserter
            ->expects($this->once())
            ->method('insert')
            ->with(
                $this->isInstanceOf(Worksheet::class),
                'signature.png',
                'D15',
                70
            );

        $config = new TimeSheetConfig('/template.xlsx', '/pdf', '/img', 'logo.png', 'signature.png');

        $workMonth = $this->createMock(WorkMonth::class);
        $workMonth->method('getFormattedTotalTime')->willReturn('123h');
        $workMonth->method('getLunchTickets')->willReturn($tickets);

        // Capturer les appels à applyFromArray
        $capturedStyles = [];

        $styleMock = $this->createMock(Style::class);
        $styleMock
            ->method('applyFromArray')
            ->willReturnCallback(function (array $style) use (&$capturedStyles, $styleMock) {
                $capturedStyles[] = $style;
                return $styleMock;
            });

        $rowDimensionMock = $this->createMock(RowDimension::class);
        $rowDimensionMock->expects($this->exactly(2))->method('setRowHeight')->with(15)->willReturnSelf();

        $sheet = $this->createMock(Worksheet::class);
        $sheet->expects($this->exactly(3))->method('setCellValue');
        $sheet->expects($this->exactly(2))->method('mergeCells');
        $sheet->expects($this->exactly(2))->method('getStyle')->willReturn($styleMock);
        $sheet->expects($this->exactly(2))->method('getRowDimension')->willReturn($rowDimensionMock);

        $context = new SheetContext();
        $context->line = 11;

        $section = new FooterSection($styleProvider, $imageInserter, $config);
        $section->apply($sheet, $workMonth, $context);

        $this->assertEquals(13, $context->line);
        $this->assertCount(2, $capturedStyles);
        $this->assertSame($styleWithBorders, $capturedStyles[0]);
        $this->assertSame($styleWithoutBorders, $capturedStyles[1]);
    }

    public static function provideLunchTicketCases(): array
    {
        return [
            'no ticket' => [0, '0 Ticket restaurant'],
            'one ticket' => [1, '1 Ticket restaurant'],
            'multiple tickets' => [3, '3 Tickets restaurants'],
        ];
    }
}
