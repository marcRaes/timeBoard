<?php

namespace App\Service\Security;

use App\Dto\ResendEmailConfirmationResultDto;
use App\Repository\UserRepository;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

readonly class ResendEmailConfirmationHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private EmailConfirmationMailer $emailConfirmationMailer,
        private RateLimiterFactoryInterface $emailLimiter
    ) {}

    /**
     * @throws TransportExceptionInterface
     */
    public function handle(string $email): ResendEmailConfirmationResultDto
    {
        if (!$email) {
            return new ResendEmailConfirmationResultDto('Adresse email requise.', 'danger');
        }

        $limiter = $this->emailLimiter->create($email);
        if (!$limiter->consume()->isAccepted()) {
            return new ResendEmailConfirmationResultDto('Vous avez récemment demandé un renvoi. Merci de patienter quelques minutes.', 'warning');
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if ($user && !$user->isVerified()) {
            $this->emailConfirmationMailer->send($user);
            return new ResendEmailConfirmationResultDto('Un nouvel email de confirmation vous a été envoyé.', 'success');
        }

        return new ResendEmailConfirmationResultDto('Si ce compte existe, un email de confirmation a déjà été envoyé.');
    }
}
