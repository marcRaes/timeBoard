<?php

namespace App\Dto;

readonly class EmailVerificationResultDto
{
    public function __construct(
        public string  $message,
        public string  $type = 'success',
        public ?string $redirectRoute = 'app_home'
    ) {}
}
