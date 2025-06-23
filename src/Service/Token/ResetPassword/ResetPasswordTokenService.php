<?php

namespace App\Service\Token\ResetPassword;

use App\Entity\User;

class ResetPasswordTokenService
{
    public function assignToken(User $user, string $token): void
    {
        $user->setResetToken($token);
        $user->setResetTokenExpiresAt(new \DateTimeImmutable('+1 hour'));
    }

    public function isValid(User $user): bool
    {
        return $user->getResetTokenExpiresAt() >= new \DateTimeImmutable();
    }

    public function revokeToken(User $user): void
    {
        $user->setResetToken(null);
        $user->setResetTokenExpiresAt(null);
    }
}
