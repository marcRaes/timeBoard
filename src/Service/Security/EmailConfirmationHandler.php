<?php

namespace App\Service\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

readonly class EmailConfirmationHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private Security $security,
    ) {}

    public function handleEmailConfirmation(Request $request, User $user): void
    {
        $this->verifyEmailHelper->validateEmailConfirmationFromRequest(
            $request,
            (string) $user->getId(),
            $user->getEmail()
        );

        $user->setIsVerified(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->security->login($user);
    }
}
