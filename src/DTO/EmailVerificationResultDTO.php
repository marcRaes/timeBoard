<?php

namespace App\DTO;

readonly class EmailVerificationResultDTO
{
    public function __construct(
        public string  $message,
        public string  $type = 'success',
        public ?string $redirectRoute = 'app_home'
    ) {}
}
