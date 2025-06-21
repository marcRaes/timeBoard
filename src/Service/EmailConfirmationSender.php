<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use App\Security\EmailVerifier;

readonly class EmailConfirmationSender
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
