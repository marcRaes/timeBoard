<?php

namespace App\Service\Security;

use App\Dto\RegistrationDto;
use App\Factory\UserFactory;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

readonly class RegistrationHandler
{
    public function __construct(
        private UserFactory $userFactory,
        private UserPersister $userPersister,
        private EmailConfirmationMailer $mailer,
    ) {}

    /**
     * @throws TransportExceptionInterface
     */
    public function register(RegistrationDto $dto): void
    {
        $user = $this->userFactory->fromDto($dto);
        $this->userPersister->save($user);
        $this->mailer->send($user);
    }
}
