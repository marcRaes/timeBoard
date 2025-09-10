<?php

namespace App\Tests\Service\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Security\EmailConfirmationMailer;
use App\Service\Security\ResendEmailConfirmationHandler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

class ResendEmailConfirmationHandlerTest extends TestCase
{
    private UserRepository $userRepository;
    private EmailConfirmationMailer $emailConfirmationMailer;
    private RateLimiterFactoryInterface $emailLimiter;
    private LimiterInterface $limiter;
    private RateLimit $rateLimit;
    private ResendEmailConfirmationHandler $handler;
    private User $user;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->emailConfirmationMailer = $this->createMock(EmailConfirmationMailer::class);
        $this->emailLimiter = $this->createMock(RateLimiterFactoryInterface::class);
        $this->limiter = $this->createMock(LimiterInterface::class);
        $this->rateLimit = $this->createMock(RateLimit::class);
        $this->user = $this->createMock(User::class);

        $this->handler = new ResendEmailConfirmationHandler(
            $this->userRepository,
            $this->emailConfirmationMailer,
            $this->emailLimiter
        );
    }

    /**
     * @throws Exception
     */
    #[DataProvider('provideVerifyCases')]
    public function testHandle(
        string $email,
        bool $userVerified,
        bool $limiterAccepted,
        ?bool $expectSend,
        ?string $expectedType,
        ?string $expectedMessage,
        bool $expectException
    ): void
    {
        if ($expectException) {
            $this->expectException(TransportExceptionInterface::class);
        }

        if ($email === '') {
            $this->emailLimiter->expects($this->never())
                ->method('create')
            ;

            $this->emailConfirmationMailer->expects($this->never())
                ->method('send')
            ;
        } else {
            $this->mockLimiter($email, $limiterAccepted);

            if ($limiterAccepted) {
                $this->mockUser($userVerified);

                $this->userRepository->expects($this->once())
                    ->method('findOneBy')
                    ->with(['email' => $email])
                    ->willReturn($this->user)
                ;
            } else {
                $this->emailConfirmationMailer->expects($this->never())
                    ->method('send')
                ;
            }

            if ($expectSend === true) {
                $sendExpectation = $this->emailConfirmationMailer->expects($this->once())
                    ->method('send')
                    ->with($this->user);

                if ($expectException) {
                    $sendExpectation->willThrowException(
                        $this->createMock(TransportExceptionInterface::class)
                    );
                }
            } elseif ($expectSend === false) {
                $this->emailConfirmationMailer->expects($this->never())
                    ->method('send')
                ;
            }
        }

        if (!$expectException) {
            $result = $this->handler->handle($email);
            $this->assertEquals($expectedMessage, $result->message);
            $this->assertEquals($expectedType, $result->type);
        } else {
            $this->handler->handle($email);
        }
    }

    public static function provideVerifyCases(): array
    {
        return [
            'email empty' => [
                'email' => '',
                'userVerified' => true,
                'limiterAccepted' => false,
                'expectSend' => false,
                'expectedType' => 'danger',
                'expectedMessage' => 'Adresse email requise.',
                'expectException' => false,
            ],
            'limit exceeded' => [
                'email' => 'test@example.fr',
                'userVerified' => false,
                'limiterAccepted' => false,
                'expectSend' => false,
                'expectedType' => 'warning',
                'expectedMessage' => 'Vous avez récemment demandé un renvoi. Merci de patienter quelques minutes.',
                'expectException' => false,
            ],
            'limiter accepted, user not verified' => [
                'email' => 'test@example.fr',
                'userVerified' => false,
                'limiterAccepted' => true,
                'expectSend' => true,
                'expectedType' => 'success',
                'expectedMessage' => 'Un nouvel email de confirmation vous a été envoyé.',
                'expectException' => false,
            ],
            'limiter accepted, user already verified' => [
                'email' => 'test@example.fr',
                'userVerified' => true,
                'limiterAccepted' => true,
                'expectSend' => false,
                'expectedType' => 'info',
                'expectedMessage' => 'Si ce compte existe, un email de confirmation a déjà été envoyé.',
                'expectException' => false,
            ],
            'email not verified but mailer throws exception' => [
                'email' => 'test@example.fr',
                'userVerified' => false,
                'limiterAccepted' => true,
                'expectSend' => true,
                'expectedType' => null,
                'expectedMessage' => null,
                'expectException' => true,
            ],
        ];
    }

    private function mockLimiter(string $email, bool $accepted): void
    {
        $this->rateLimit->expects($this->once())
            ->method('isAccepted')
            ->willReturn($accepted)
        ;

        $this->limiter->expects($this->once())
            ->method('consume')
            ->willReturn($this->rateLimit)
        ;

        $this->emailLimiter->expects($this->once())
            ->method('create')
            ->with($email)->willReturn($this->limiter)
        ;
    }

    private function mockUser(bool $verified): void
    {
        $this->user->expects($this->once())
            ->method('isVerified')
            ->willReturn($verified)
        ;
    }
}
