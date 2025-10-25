<?php

namespace App\Service\File;

class TemporaryFileCleaner
{
    public function clean($dirTemp): void
    {
        foreach ($dirTemp as $tempFile) {
            if ($tempFile !== null && is_file($tempFile)) {
                @unlink($tempFile);
            }
        }
    }
}
