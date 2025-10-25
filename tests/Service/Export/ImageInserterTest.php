<?php

namespace App\Tests\Service\Export;

use App\Config\TimeSheetConfig;
use App\Exception\ImageNotFoundException;
use App\Service\Export\ImageInserter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ImageInserterTest extends TestCase
{
    private string $imgDir;
    private const MINIMAL_PNG_BASE64 = <<<BASE64
iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=
BASE64;

    protected function setUp(): void
    {
        $this->imgDir = sys_get_temp_dir() . '/img_inserter_test/';
        if (!is_dir($this->imgDir)) {
            mkdir($this->imgDir, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->imgDir . '*'));
        @rmdir($this->imgDir);
    }

    #[DataProvider('provideInsertionData')]
    public function testInsertAddsDrawingWithCorrectProperties(
        string $filename,
        string $coordinates,
        int $height
    ): void {
        $fullPath = $this->imgDir . $filename;
        file_put_contents($fullPath, base64_decode(self::MINIMAL_PNG_BASE64));

        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $inserter = new ImageInserter();
        $inserter->insert($worksheet, $fullPath, $coordinates, $height);

        $drawings = $worksheet->getDrawingCollection();
        $this->assertCount(1, $drawings);

        $drawing = $drawings[0];

        $this->assertInstanceOf(Drawing::class, $drawing);
        $this->assertSame(pathinfo($filename, PATHINFO_FILENAME), $drawing->getName());
        $this->assertSame($filename, $drawing->getDescription());
        $this->assertSame($fullPath, $drawing->getPath());
        $this->assertSame($coordinates, $drawing->getCoordinates());
        $this->assertSame($height, $drawing->getHeight());
        $this->assertTrue($drawing->getShadow()->getVisible());
    }

    public static function provideInsertionData(): array
    {
        return [
            'standard logo' => [
                'logo.png',
                'B2',
                42
            ],
            'large logo' => [
                'header_image.png',
                'D5',
                100
            ],
            'small logo' => [
                'mini_icon.png',
                'A1',
                20
            ],
            'another position' => [
                'alt_logo.png',
                'E7',
                55
            ],
        ];
    }

    public function testInsertThrowsCustomExceptionIfImageIsMissing(): void
    {
        $fullPath = $this->imgDir . 'missing.png';
        $worksheet = (new Spreadsheet())->getActiveSheet();
        $inserter = new ImageInserter();

        $this->expectException(ImageNotFoundException::class);
        $this->expectExceptionMessage('Image file "' . $this->imgDir . 'missing.png" not found.');

        $inserter->insert($worksheet, $fullPath, 'C3', 60);
    }
}
