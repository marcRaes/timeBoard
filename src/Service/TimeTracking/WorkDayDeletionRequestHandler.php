<?php

namespace App\Service\TimeTracking;

use App\Entity\WorkDay;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final readonly class WorkDayDeletionRequestHandler
{
    public function __construct(
        private CsrfTokenManagerInterface $csrfTokenManager,
        private AuthorizationCheckerInterface $authorizationChecker,
        private WorkDayDeleter $workDayDeleter,
    ) {}

    public function handle(Request $request, WorkDay $workDay): void
    {
        if (!$this->csrfTokenManager->isTokenValid(
            new CsrfToken('delete' . $workDay->getId(), $request->get('_token'))
        )) {
            throw new AccessDeniedHttpException('Jeton CSRF invalide.');
        }

        if (!$this->authorizationChecker->isGranted('DELETE', $workDay)) {
            throw new AccessDeniedHttpException('Suppression non autorisée.');
        }

        $this->workDayDeleter->delete($workDay);
    }
}
