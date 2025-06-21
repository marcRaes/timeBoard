<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\{UserCheckerInterface, UserInterface};

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (! $user instanceof User) {
            return;
        }

        if (! $user->isVerified()) {
            throw new CustomUserMessageAuthenticationException('email_verification_required');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {}
}
