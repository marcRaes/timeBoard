<?php

namespace App\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

class MonthNameHelper
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getLocalizedMonthName(int $month): string
    {
        $date = \DateTime::createFromFormat('!m', $month);

        return $this->translator->trans($date->format('F'));
    }
}
