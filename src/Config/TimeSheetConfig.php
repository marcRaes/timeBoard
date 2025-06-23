<?php

namespace App\Config;

readonly class TimeSheetConfig
{
    public function __construct(
        public string $templatePath,
        public string $pdfPath,
        public string $imgPath,
        public string $logoFilename,
        public string $signatureFilename,
    ) {}
}
