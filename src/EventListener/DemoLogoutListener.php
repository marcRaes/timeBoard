<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\LogoutEvent;

readonly class DemoLogoutListener
{
    public function __construct(
        private RequestStack $requestStack,
        private bool $modeDemo
    ) {}

    public function onLogout(LogoutEvent $event): void
    {
        if (!$this->modeDemo) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        $request->getSession()->getFlashBag()->add('warning', 'Impossible en mode Demo.');
    }
}
