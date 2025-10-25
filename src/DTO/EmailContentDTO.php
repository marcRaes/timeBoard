<?php

namespace App\DTO;

readonly class EmailContentDTO
{
    public function __construct(
        public string $subject,
        public string $htmlBody,
        public string $recipientEmail,
        public string $attachmentPath,
        public ?string $transportProof
    )
    {}
}
