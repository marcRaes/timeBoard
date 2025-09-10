<?php

namespace App\Tests\Service;

use App\Service\MonthNameHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class MonthNameHelperTest extends TestCase
{
    public static function dataMonths(): array
    {
        return [
            'janvier' => [1, 'January', 'Janvier'],
            'mars' => [3, 'March', 'Mars'],
            'mai' => [5, 'May', 'Mai'],
        ];
    }

    /**
     * @throws Exception
     */
    #[DataProvider('dataMonths')]
    public function testGetLocalizedMonthName(int $monthNumber, string $monthKey, string $expectedTranslation): void
    {
        $translator = $this->createMock(TranslatorInterface::class);

        $translator->expects($this->once())->method('trans')->with($monthKey)->willReturn($expectedTranslation);

        $helper = new MonthNameHelper($translator);

        $this->assertSame($expectedTranslation, $helper->getLocalizedMonthName($monthNumber));
    }
}
