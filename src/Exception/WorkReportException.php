<?php

namespace App\Exception;

class WorkReportException extends \RuntimeException
{
    public function getUserMessage(): string
    {
        return $this->getMessage() . '<br>Si le problème persiste, contactez votre administrateur.';
    }

    public function getField(): ?string
    {
        return null;
    }
}
