<?php

namespace App\Tests\Service\Export;

use App\Service\Export\PdfExporter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PHPUnit\Framework\TestCase;

class PdfExporterTest extends TestCase
{
    private string $filePath;

    protected function setUp(): void
    {
        $this->filePath = sys_get_temp_dir() . '/test_pdf_export_' . uniqid() . '.pdf';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->filePath)) {
            unlink($this->filePath);
        }
    }

    public function testWriteGeneratesPdfFile(): void
    {
        $spreadsheet = new Spreadsheet();
        $exporter = new PdfExporter();

        $exporter->write($spreadsheet, $this->filePath);

        $this->assertFileExists($this->filePath);
        $this->assertGreaterThan(1000, filesize($this->filePath), 'Le fichier PDF est vide ou trop petit.');
    }
}
