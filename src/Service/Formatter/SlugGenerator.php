<?php

namespace App\Service\Formatter;

use Normalizer;

readonly class SlugGenerator
{
    public function slugify(string $text): string
    {
        // 1. Normalise
        $text = Normalizer::normalize($text, Normalizer::FORM_D);
        // 2. Supprime les accents
        $text = preg_replace('/[\p{Mn}]/u', '', $text);
        // 3. Remplace les espaces / ponctuations par des underscores
        $text = preg_replace('/[^a-zA-Z0-9]+/u', '_', $text);

        return strtolower(trim($text, '_'));
    }
}
