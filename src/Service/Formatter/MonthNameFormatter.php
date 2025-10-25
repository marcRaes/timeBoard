<?php

namespace App\Service\Formatter;

use Symfony\Contracts\Translation\TranslatorInterface;

class MonthNameFormatter
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
