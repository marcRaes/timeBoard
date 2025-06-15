<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

readonly class ResetPasswordTokenManager
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $em
    ) {}

    public function generate(User $user): string
    {
        $token = Uuid::v4()->toRfc4122();
        $user->setResetToken($token);
        $user->setResetTokenExpiresAt(new \DateTimeImmutable('+1 hour'));

        $this->em->flush();

        return $token;
    }

    public function validate(string $token): ?User
    {
        $user = $this->userRepository->findOneBy(['resetToken' => $token]);

        if (!$user || $user->getResetTokenExpiresAt() < new \DateTimeImmutable()) {
            return null;
        }

        return $user;
    }

    public function invalidate(User $user): void
    {
        $user->setResetToken(null);
        $user->setResetTokenExpiresAt(null);
        $this->em->flush();
    }
}
