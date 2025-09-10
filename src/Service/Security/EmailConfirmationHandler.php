<?php

namespace App\Service\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

readonly class EmailConfirmationHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EmailConfirmationValidatorInterface $emailValidator,
        private Security $security,
    ) {}

    public function handleEmailConfirmation(Request $request, User $user): void
    {
        $this->emailValidator->validate(
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
