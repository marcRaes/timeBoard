<?php

namespace App\Service;

use App\Dto\RegistrationDto;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class RegistrationManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $userPasswordHasher,
        private EmailConfirmationSender $emailConfirmationSender
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

        $this->emailConfirmationSender->send($user);
    }
}
