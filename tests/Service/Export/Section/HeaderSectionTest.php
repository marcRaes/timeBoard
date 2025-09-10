<?php

namespace App\Tests\Service\Export\Section;

use App\Config\TimeSheetConfig;
use App\Entity\User;
use App\Entity\WorkMonth;
use App\Service\Export\ImageInserter;
use App\Service\Export\Section\HeaderSection;
use App\Service\Export\Section\SheetContext;
use App\Service\MonthNameHelper;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class HeaderSectionTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testApplySetsHeaderCellsAndInsertsLogo(): void
    {
        $imageInserter = $this->createMock(ImageInserter::class);
        $config = new TimeSheetConfig('/template.xlsx', '/pdf', '/img', 'logo.png', 'sign.png');
        $monthNameHelper = $this->createMock(MonthNameHelper::class);

        $sheet = $this->createMock(Worksheet::class);

        $user = $this->createMock(User::class);
        $user->method('getFirstName')->willReturn('Luke');
        $user->method('getLastName')->willReturn('Skywalker');

        $workMonth = $this->createMock(WorkMonth::class);
        $workMonth->method('getMonth')->willReturn(5);
        $workMonth->method('getYear')->willReturn(2025);
        $workMonth->method('getUser')->willReturn($user);

        $monthNameHelper
            ->method('getLocalizedMonthName')
            ->with(5)
            ->willReturn('Mai');

        $calledArguments = [];

        $sheet->method('setCellValue')
            ->willReturnCallback(function (...$args) use (&$calledArguments, $sheet) {
                $calledArguments[] = $args;
                return $sheet;
            });

        $imageInserter->expects($this->once())
            ->method('insert')
            ->with($sheet, 'logo.png', 'A2', 70);

        $context = new SheetContext();
        $section = new HeaderSection($imageInserter, $config, $monthNameHelper);
        $section->apply($sheet, $workMonth, $context);

        $this->assertContains(['E4', 'Mai', null], $calledArguments);
        $this->assertContains(['H4', 2025, null], $calledArguments);
        $this->assertContains(['B8', 'Skywalker', null], $calledArguments);
        $this->assertContains(['B10', 'Luke', null], $calledArguments);
    }
}
