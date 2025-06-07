<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\{UserCheckerInterface, UserInterface};

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (! $user instanceof User) {
            return;
        }

        if (! $user->isVerified()) {
            throw new CustomUserMessageAccountStatusException(
                'Vous devez confirmer votre adresse email avant de vous connecter.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {}
}
