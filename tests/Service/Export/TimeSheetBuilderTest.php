<?php

namespace App\Tests\Service\Export;

use App\Config\TimeSheetConfig;
use App\Entity\WorkMonth;
use App\Service\Export\Section\SheetContext;
use App\Service\Export\Section\TimeSheetSectionInterface;
use App\Service\Export\TimeSheetBuilder;
use App\Service\Export\TimeSheetConfigurator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class TimeSheetBuilderTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testBuildGeneratesSpreadsheetFromTemplate(): void
    {
        $templatePath = __DIR__ . '/../../Fixtures/template_test.xlsx';
        $config = new TimeSheetConfig(
            $templatePath,
            '/tmp/pdf',
            '/tmp/img',
            'logo.png',
            'sign.png'
        );

        $context = new SheetContext();

        $section1 = $this->createMock(TimeSheetSectionInterface::class);
        $section2 = $this->createMock(TimeSheetSectionInterface::class);

        $sheetMatcher = $this->callback(fn($sheet) => $sheet->getTitle() === 'Feuille1');
        $monthMock = $this->createMock(WorkMonth::class);

        $section1->expects($this->once())
            ->method('apply')
            ->with($sheetMatcher, $monthMock, $context);

        $section2->expects($this->once())
            ->method('apply')
            ->with($sheetMatcher, $monthMock, $context);

        $configurator = $this->createMock(TimeSheetConfigurator::class);
        $configurator->expects($this->once())
            ->method('configure')
            ->with($sheetMatcher);

        $builder = new TimeSheetBuilder([$section1, $section2], $config, $context, $configurator);

        $spreadsheet = $builder->build($monthMock);

        $this->assertInstanceOf(Spreadsheet::class, $spreadsheet);
        $this->assertEquals('Feuille1', $spreadsheet->getActiveSheet()->getTitle());
    }
}
