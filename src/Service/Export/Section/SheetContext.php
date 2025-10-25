<?php

namespace App\Service\Export\Section;

class SheetContext
{
    public int $line = 15;
    private ?string $signatureData = null;

    public function advance(int $n = 1): void
    {
        $this->line += $n;
    }

    public function setSignatureData(?string $signatureData): void
    {
        $this->signatureData = $signatureData;
    }


    public function getSignatureData(): ?string
    {
        return $this->signatureData;
    }
}
