<?php

namespace App\Service\Security;

use App\DTO\EmailVerificationResultDTO;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

readonly class EmailVerificationHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EmailConfirmationHandler $emailConfirmationHandler,
    ) {}

    public function verify(Request $request, string $userId): EmailVerificationResultDTO
    {
        $user = $this->entityManager->getRepository(User::class)->find($userId);

        if (!$user) {
            return new EmailVerificationResultDTO('Utilisateur introuvable.', 'error', 'app_login');
        }

        try {
            $this->emailConfirmationHandler->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface) {
            return new EmailVerificationResultDTO('Le lien de confirmation est invalide ou expiré.', 'error', 'app_login');
        }

        return new EmailVerificationResultDTO('Votre adresse email est confirmée et vous êtes maintenant connecté !', 'success', 'app_home');
    }
}
