<?php

namespace App\Factory;

use App\Dto\RegistrationDto;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class UserFactory
{
    public function __construct(
        private UserPasswordHasherInterface $hasher
    ) {}

    public function fromDto(RegistrationDto $dto): User
    {
        $user = new User();
        $user->setFirstName($dto->firstName);
        $user->setLastName($dto->lastName);
        $user->setEmail($dto->email);
        $user->setPassword($this->hasher->hashPassword($user, $dto->password->password));

        return $user;
    }
}
