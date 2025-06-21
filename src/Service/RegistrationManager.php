<?php

namespace App\Service;

use App\Dto\RegistrationDto;
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
    public function register(RegistrationDto $dto): void
    {
        $user = new User();
        $user->setFirstName($dto->firstName);
        $user->setLastName($dto->lastName);
        $user->setEmail($dto->email);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $dto->password->password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address('contact@marcraes.fr', 'TimeBoard'))
                ->to((string) $user->getEmail())
                ->subject('Veuillez confirmer votre email')
                ->htmlTemplate('emails/confirmation_email.html.twig')
        );
    }
}
