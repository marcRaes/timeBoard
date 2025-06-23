<?php

namespace App\Service\Security;

use App\Dto\EmailVerificationResultDto;
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

    public function verify(Request $request, string $userId): EmailVerificationResultDto
    {
        $user = $this->entityManager->getRepository(User::class)->find($userId);

        if (!$user) {
            return new EmailVerificationResultDto('Utilisateur introuvable.', 'error', 'app_login');
        }

        try {
            $this->emailConfirmationHandler->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface) {
            return new EmailVerificationResultDto('Le lien de confirmation est invalide ou expiré.', 'error', 'app_login');
        }

        return new EmailVerificationResultDto('Votre adresse email est confirmée et vous êtes maintenant connecté !', 'success', 'app_home');
    }
}
