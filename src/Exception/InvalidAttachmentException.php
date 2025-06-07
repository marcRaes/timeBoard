<?php

namespace App\Exception;

class InvalidAttachmentException extends WorkReportException
{
    public function getUserMessage(): string
    {
        return $this->getMessage();
    }

    public function getField(): ?string
    {
        return 'attachmentPath';
    }
}
