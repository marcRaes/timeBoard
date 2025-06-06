<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\WorkMonth;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class WorkMonthVoter extends Voter
{
    public const VIEW = 'VIEW';

    protected function supports(string $attribute, $subject): bool
    {
        return $attribute === self::VIEW && $subject instanceof WorkMonth;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $workMonth = $subject;
        if (!$workMonth instanceof WorkMonth) {
            return false;
        }

        if ($attribute === self::VIEW) {
            return $workMonth->getUser() === $user;
        }

        return false;
    }
}
