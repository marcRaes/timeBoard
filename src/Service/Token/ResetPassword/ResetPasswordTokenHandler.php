<?php

namespace App\Service\Token\ResetPassword;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class ResetPasswordTokenHandler
{
    public function __construct(
        private ResetPasswordTokenGenerator $tokenGenerator,
        private ResetPasswordTokenService $tokenService,
        private UserRepository $userRepository,
        private EntityManagerInterface $em,
    ) {}

    public function generate(User $user): string
    {
        $token = $this->tokenGenerator->generate();
        $this->tokenService->assignToken($user, $token);
        $this->em->flush();

        return $token;
    }

    public function validate(string $token): ?User
    {
        $user = $this->userRepository->findOneBy(['resetToken' => $token]);

        if (!$user || !$this->tokenService->isValid($user)) {
            return null;
        }

        return $user;
    }

    public function invalidate(User $user): void
    {
        $this->tokenService->revokeToken($user);
        $this->em->flush();
    }
}
