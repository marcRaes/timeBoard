<?php

namespace App\Service\Export;

use App\Exception\ImageNotFoundException;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

readonly class ImageInserter
{
    public function __construct() {}

    public function insert(Worksheet $sheet, string $fullPath, string $coordinates, int $height): void
    {
        if (!file_exists($fullPath)) {
            throw new ImageNotFoundException($fullPath);
        }

        $drawing = new Drawing();
        $drawing->setWorksheet($sheet);
        $drawing->setName(pathinfo($fullPath, PATHINFO_FILENAME));
        $drawing->setDescription(basename($fullPath));
        $drawing->setPath($fullPath);
        $drawing->setHeight($height);
        $drawing->setCoordinates($coordinates);
        $drawing->getShadow()->setVisible(true);
    }
}
