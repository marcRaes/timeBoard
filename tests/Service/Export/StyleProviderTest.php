<?php

namespace App\Tests\Service\Export;

use App\Service\Export\StyleProvider;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PHPUnit\Framework\TestCase;

class StyleProviderTest extends TestCase
{
    public function testGetDefaultStyleReturnsExpectedStructure(): void
    {
        $provider = new StyleProvider();

        $expected = [
            'font' => ['bold' => false],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ];

        $this->assertSame($expected, $provider->getDefaultStyle());
    }

    public function testGetDefaultStyleWithBold(): void
    {
        $provider = new StyleProvider();

        $style = $provider->getDefaultStyle(true);
        $this->assertTrue($style['font']['bold']);
    }
}
