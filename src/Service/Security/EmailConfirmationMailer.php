<?php

namespace App\Service\Security;

use App\Entity\User;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;

readonly class EmailConfirmationMailer
{
    public function __construct(
        private EmailVerifier $emailVerifier
    ) {}

    /**
     * @throws TransportExceptionInterface
     */
    public function send(User $user): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@marcraes.fr', 'TimeBoard'))
            ->to((string) $user->getEmail())
            ->subject('Veuillez confirmer votre email')
            ->htmlTemplate('emails/confirmation_email.html.twig');

        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user, $email);
    }
}
