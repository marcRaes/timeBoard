<?php

namespace App\Tests\Service\Export\Section;

use App\Config\TimeSheetConfig;
use App\Entity\User;
use App\Entity\WorkMonth;
use App\Service\Export\ImageInserter;
use App\Service\Export\Section\HeaderSection;
use App\Service\Export\Section\SheetContext;
use App\Service\Formatter\MonthNameFormatter;
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
        $config = new TimeSheetConfig('/template.xlsx', '/pdf', '/img/', 'logo.png');
        $monthNameHelper = $this->createMock(MonthNameFormatter::class);

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
            ->with(
                $sheet,
                '/img/logo.png',
                'A2',
                70
            );

        $context = new SheetContext();
        $section = new HeaderSection($imageInserter, $config, $monthNameHelper);
        $section->apply($sheet, $workMonth, $context);

        $this->assertTrue($this->cellWasSet($calledArguments, 'E4', 'Mai'), 'La cellule E4 doit contenir le nom du mois.');
        $this->assertTrue($this->cellWasSet($calledArguments, 'H4', 2025), 'La cellule H4 doit contenir l’année.');
        $this->assertTrue($this->cellWasSet($calledArguments, 'B8', 'Skywalker'), 'La cellule B8 doit contenir le nom de famille.');
        $this->assertTrue($this->cellWasSet($calledArguments, 'B10', 'Luke'), 'La cellule B10 doit contenir le prénom.');
    }

    private function cellWasSet(array $calledArguments, string $cell, mixed $expectedValue): bool
    {
        foreach ($calledArguments as $call) {
            if ($call[0] === $cell && $call[1] === $expectedValue) {
                return true;
            }
        }
        return false;
    }
}
