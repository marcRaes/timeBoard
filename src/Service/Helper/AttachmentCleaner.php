<?php

namespace App\Service\Helper;

readonly class AttachmentCleaner
{
    public function cleanup(?string $path): void
    {
        if ($path !== null && is_file($path)) {
            @unlink($path);
        }
    }
}
