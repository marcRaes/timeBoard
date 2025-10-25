<?php

namespace App\Service\Formatter;

readonly class MimeTypeExtensionMapper
{
    private const MIME_TYPE_MAP = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
    ];

    public function map(string $mimeType): string
    {
        return self::MIME_TYPE_MAP[$mimeType] ?? 'bin';
    }
}
