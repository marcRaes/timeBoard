<?php

namespace App\Tests\Service;

use App\Dto\WorkMonthSummaryDto;
use App\Entity\WorkMonth;
use App\Service\WorkMonthGrouper;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

class WorkMonthGrouperTest extends TestCase
{
    private array $result;

    protected function setUp(): void
    {
        $workMonths = [
            (new WorkMonth)->setYear(2022)->setMonth(3),
            (new WorkMonth)->setYear(2025)->setMonth(5),
            (new WorkMonth)->setYear(2024)->setMonth(9),
            (new WorkMonth)->setYear(2023)->setMonth(12),
            (new WorkMonth)->setYear(2025)->setMonth(11),
            (new WorkMonth)->setYear(2023)->setMonth(7),
            (new WorkMonth)->setYear(2022)->setMonth(8),
            (new WorkMonth)->setYear(2024)->setMonth(1),
        ];
        $this->result = (new WorkMonthGrouper())->groupByYearAndMonth($workMonths);
    }

    public function testGroupByYearAndMonthOrderYear(): void
    {
        $this->assertSame([2025, 2024, 2023, 2022], array_keys($this->result));
    }

    public function testGroupByYearAndMonth2025(): void
    {
        $this->assertSame([11, 5], array_keys($this->result[2025]));
    }

    public function testGroupByYearAndMonth2024(): void
    {
        $this->assertSame([9, 1], array_keys($this->result[2024]));
    }

    public function testGroupByYearAndMonth2023(): void
    {
        $this->assertSame([12, 7], array_keys($this->result[2023]));
    }

    public function testGroupByYearAndMonth2022(): void
    {
        $this->assertSame([8, 3], array_keys($this->result[2022]));
    }

    public function testGroupByYearAndMonthType(): void
    {
        $this->assertInstanceOf(WorkMonthSummaryDto::class, $this->result[2025][11]);
    }

    public static function tearDownAfterClass(): void
    {
        $expected = 6;
        $actual = count(array_filter(
            get_class_methods(static::class),
            fn($method) => str_starts_with($method, 'test')
        ));

        if ($actual !== $expected) {
            throw new AssertionFailedError("Expected $expected tests, found $actual");
        }
    }
}
