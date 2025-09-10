<?php

namespace App\Tests\Service\Export\Section;

use App\Entity\WorkMonth;
use App\Entity\WorkDay;
use App\Entity\WorkPeriod;
use App\Service\Export\Section\LinesSection;
use App\Service\Export\Section\SheetContext;
use App\Service\Export\StyleProvider;
use App\Service\WorkDurationFormatter;
use Doctrine\Common\Collections\ArrayCollection;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\RowDimension;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LinesSectionTest extends TestCase
{
    /**
     * @throws Exception
     * @throws \Exception
     */
    #[DataProvider('provideWorkData')]
    public function testApplyGeneratesCorrectLines(
        string $date,
        string $start,
        string $end,
        int $duration,
        string $location,
        ?string $replacedAgent,
        array $expectedRow
    ): void {
        $style = ['font' => ['bold' => true]];

        /** @var StyleProvider&MockObject $styleProvider */
        $styleProvider = $this->createMock(StyleProvider::class);
        $styleProvider->method('getDefaultStyle')->willReturn($style);

        /** @var WorkDurationFormatter&MockObject $formatter */
        $formatter = $this->createMock(WorkDurationFormatter::class);
        $formatter->method('format')->with($duration)->willReturn('1h30');

        $section = new LinesSection($styleProvider, $formatter);

        /** @var Worksheet&MockObject $sheet */
        $sheet = $this->createMock(Worksheet::class);

        $sheet->expects($this->once())
            ->method('fromArray')
            ->with($expectedRow, null, 'A5');

        $calledMergeCells = [];

        $sheet->method('mergeCells')
            ->willReturnCallback(function (string $range) use (&$calledMergeCells, $sheet) {
                $calledMergeCells[] = $range;
                return $sheet;
            });

        $styleMock = $this->createMock(Style::class);
        $styleMock->expects($this->once())
            ->method('applyFromArray')
            ->with(['font' => ['bold' => true]])
            ->willReturnSelf();

        $sheet->expects($this->once())
            ->method('getStyle')
            ->with('A5:N5')
            ->willReturn($styleMock);

        $rowDimension = $this->createMock(RowDimension::class);
        $rowDimension->expects($this->once())->method('setRowHeight')->with(15);

        $sheet->expects($this->once())
            ->method('getRowDimension')
            ->with(5)
            ->willReturn($rowDimension);

        $workPeriod = $this->createConfiguredMock(WorkPeriod::class, [
            'getTimeStart' => new \DateTimeImmutable($start),
            'getTimeEnd' => new \DateTimeImmutable($end),
            'getDuration' => $duration,
            'getLocation' => $location,
            'getReplacedAgent' => $replacedAgent,
        ]);

        $workDay = $this->createConfiguredMock(WorkDay::class, [
            'getDate' => new \DateTimeImmutable($date),
            'getWorkPeriods' => new ArrayCollection([$workPeriod]),
        ]);

        $workMonth = $this->createConfiguredMock(WorkMonth::class, [
            'getWorkDays' => new ArrayCollection([$workDay]),
        ]);

        $context = new SheetContext();
        $context->line = 5;

        $section->apply($sheet, $workMonth, $context);

        $this->assertSame(['D5:E5', 'F5:G5', 'H5:N5'], $calledMergeCells);
        $this->assertSame(6, $context->line);
    }

    public static function provideWorkData(): array
    {
        return [
            'simple case' => [
                '2025-07-15',
                '08:00',
                '09:30',
                90,
                'Lyon',
                'Agent X',
                [
                    '15/07/2025',
                    '08:00',
                    '09:30',
                    '1h30',
                    '',
                    'Lyon',
                    '',
                    'Agent X'
                ]
            ],
        ];
    }
}
