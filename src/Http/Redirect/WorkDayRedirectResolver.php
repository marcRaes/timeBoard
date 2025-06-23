<?php

namespace App\Http\Redirect;

use App\Entity\WorkMonth;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class WorkDayRedirectResolver
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function resolveAfterDelete(?WorkMonth $workMonth): RedirectResponse
    {
        if (!$workMonth || !$workMonth->getId()) {
            $url = $this->urlGenerator->generate('app_home');
        } else {
            $url = $this->urlGenerator->generate('app_work_month_show', [
                'id' => $workMonth->getId(),
            ]);
        }

        return new RedirectResponse($url);
    }
}
