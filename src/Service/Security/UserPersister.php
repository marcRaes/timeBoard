<?php

namespace App\Service\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

readonly class UserPersister
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function save(User $user): void
    {
        $this->em->persist($user);
        $this->em->flush();
    }
}
