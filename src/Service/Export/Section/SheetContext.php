<?php

namespace App\Service\Export\Section;

class SheetContext
{
    public int $line = 15;

    public function advance(int $n = 1): void
    {
        $this->line += $n;
    }
}
