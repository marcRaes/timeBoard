<?php

namespace App\Dto;

readonly class ResendEmailConfirmationResultDto
{
    public function __construct(
        public string $message,
        public string $type = 'info'
    ) {}
}
