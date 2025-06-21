<?php

namespace App\Security;

use App\Entity\WorkDay;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class WorkDayVoter extends Voter
{
    public const DELETE = 'DELETE';

    protected function supports(string $attribute, $subject): bool
    {
        return $attribute === self::DELETE && $subject instanceof WorkDay;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        $workDay = $subject;
        if (!$workDay instanceof WorkDay) {
            return false;
        }

        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::DELETE => $this->canDelete($workDay, $user),
            default => false,
        };
    }

    private function canDelete(WorkDay $workDay, User $user): bool
    {
        $workMonth = $workDay->getWorkMonth();

        if (null === $workMonth) {
            return false;
        }

        return $workMonth->getUser() === $user;
    }
}
