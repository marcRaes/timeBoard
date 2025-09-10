<?php

namespace App\Tests\Service\Security;

use App\Entity\User;
use App\Service\Security\EmailConfirmationHandler;
use App\Service\Security\EmailConfirmationValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

class EmailConfirmationHandlerTest extends TestCase
{
    /**
     * @throws Exception
     */
    #[DataProvider('provideConfirmationScenarios')]
    public function testHandleEmailConfirmation(
        ?\Throwable $exceptionFromValidator,
        ?\Throwable $exceptionFromLogin,
        bool $expectSetIsVerified,
        bool $expectException
    ): void {
        $request = $this->createMock(Request::class);

        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(42);
        $user->method('getEmail')->willReturn('test@example.com');

        if ($expectSetIsVerified) {
            $user->expects($this->once())->method('setIsVerified')->with(true);
        } else {
            $user->expects($this->never())->method('setIsVerified');
        }

        $emailValidator = $this->createMock(EmailConfirmationValidatorInterface::class);
        $emailValidator
            ->method('validate')
            ->willReturnCallback(function () use ($exceptionFromValidator) {
                if ($exceptionFromValidator) {
                    throw $exceptionFromValidator;
                }
            });

        $entityManager = $this->createMock(EntityManagerInterface::class);
        if ($exceptionFromValidator) {
            $entityManager->expects($this->never())->method('persist');
            $entityManager->expects($this->never())->method('flush');
        } else {
            $entityManager->expects($this->once())->method('persist')->with($user);
            $entityManager->expects($this->once())->method('flush');
        }

        $security = $this->createMock(Security::class);
        $security
            ->method('login')
            ->willReturnCallback(function () use ($exceptionFromLogin) {
                if ($exceptionFromLogin) {
                    throw $exceptionFromLogin;
                }
            });

        $handler = new EmailConfirmationHandler($entityManager, $emailValidator, $security);

        if ($expectException) {
            $this->expectException(\Throwable::class);
        }

        $handler->handleEmailConfirmation($request, $user);
    }

    public static function provideConfirmationScenarios(): iterable
    {
        yield 'valid case' => [null, null, true, false];

        yield 'validator throws exception' => [
            new \RuntimeException('Validation failed'), null, false, true
        ];

        yield 'login throws exception' => [
            null, new \RuntimeException('Login failed'), true, true
        ];
    }
}
