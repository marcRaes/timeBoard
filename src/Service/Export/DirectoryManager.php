<?php

namespace App\Service\Export;


use App\Config\TimeSheetConfig;

readonly class DirectoryManager
{
    public function __construct(private TimeSheetConfig $timeSheetConfig) {}

    public function ensureExists(): void
    {
        if (!is_dir($this->timeSheetConfig->pdfPath)) {
            mkdir($this->timeSheetConfig->pdfPath, 0775, true);
        }
    }

    public function getPath(): string
    {
        return rtrim($this->timeSheetConfig->pdfPath, '/') . '/';
    }
}
