<?php

namespace App\Service\Export;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class StyleProvider
{
    public function getDefaultStyle(bool $bold = false): array
    {
        return [
            'font' => ['bold' => $bold],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ];
    }
}
