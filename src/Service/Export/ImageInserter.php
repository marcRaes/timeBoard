<?php

namespace App\Service\Export;

use App\Config\TimeSheetConfig;
use App\Exception\ImageNotFoundException;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

readonly class ImageInserter
{
    public function __construct(private TimeSheetConfig $timeSheetConfig) {}

    public function insert(Worksheet $sheet, string $filename, string $coordinates, int $height): void
    {
        $fullPath = $this->timeSheetConfig->imgPath . $filename;

        if (!file_exists($fullPath)) {
            throw new ImageNotFoundException($fullPath);
        }

        $drawing = new Drawing();
        $drawing->setWorksheet($sheet);
        $drawing->setName(pathinfo($filename, PATHINFO_FILENAME));
        $drawing->setDescription($filename);
        $drawing->setPath($fullPath);
        $drawing->setHeight($height);
        $drawing->setCoordinates($coordinates);
        $drawing->getShadow()->setVisible(true);
    }
}
