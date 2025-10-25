<?php

namespace App\DTO;

readonly class TransportProofDTO
{
    public function __construct(
        public string $path,
        public string $filename,
        public string $mimeType
    )
    {}
}
