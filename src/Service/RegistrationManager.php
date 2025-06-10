<?php

namespace App\Service;

use App\Entity\User;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class RegistrationManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly EmailVerifier $emailVerifier,
    ) {}

    /**
     * @throws TransportExceptionInterface
     */
    public function register(User $user, string $plainPassword): void
    {
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $plainPassword));
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address('contact@marcraes.fr', 'Time Board'))
                ->to((string) $user->getEmail())
                ->subject('Veuillez confirmer votre email')
                ->htmlTemplate('emails/confirmation_email.html.twig')
        );
    }
}
