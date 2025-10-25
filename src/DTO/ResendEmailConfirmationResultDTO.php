<?php

namespace App\DTO;

readonly class ResendEmailConfirmationResultDTO
{
    public function __construct(
        public string $message,
        public string $type = 'info'
    ) {}
}
