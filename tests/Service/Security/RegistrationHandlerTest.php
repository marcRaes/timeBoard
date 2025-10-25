<?php

namespace App\Tests\Service\Security;

use App\DTO\RegistrationDTO;
use App\Entity\User;
use App\Factory\UserFactory;
use App\Service\Security\EmailConfirmationMailer;
use App\Service\Security\RegistrationHandler;
use App\Service\Security\UserPersister;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class RegistrationHandlerTest extends TestCase
{
    /**
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    public function testRegister(): void
    {
        $dto = $this->createMock(RegistrationDTO::class);
        $user = $this->createMock(User::class);
        $userFactory = $this->createMock(UserFactory::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $mailer = $this->createMock(EmailConfirmationMailer::class);
        $userPersister = new UserPersister($entityManager);

        $userFactory->expects($this->once())
            ->method('fromDto')
            ->with($dto)
            ->willReturn($user)
        ;

        $entityManager->expects($this->once())->method('persist')->with($user);
        $entityManager->expects($this->once())->method('flush');

        $mailer->expects($this->once())
            ->method('send')
            ->with($user)
        ;

        $registrationHandler = new RegistrationHandler(
            $userFactory,
            $userPersister,
            $mailer
        );

        $registrationHandler->register($dto);
    }
}
