<?php

namespace App\Tests\Service\Security;

use App\DTO\EmailVerificationResultDTO;
use App\Entity\User;
use App\Service\Security\EmailConfirmationHandler;
use App\Service\Security\EmailVerificationHandler;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class EmailVerificationHandlerTest extends TestCase
{
    private EmailVerificationHandler $handler;
    private EntityManagerInterface $entityManager;
    private EmailConfirmationHandler $emailConfirmationHandler;
    private EntityRepository $userRepository;
    private Request $request;
    private User $user;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->emailConfirmationHandler = $this->createMock(EmailConfirmationHandler::class);
        $this->userRepository = $this->createMock(EntityRepository::class);
        $this->request = $this->createMock(Request::class);
        $this->user = $this->createMock(User::class);

        $this->handler = new EmailVerificationHandler(
            $this->entityManager,
            $this->emailConfirmationHandler
        );
    }

    /**
     * @throws Exception
     */
    #[DataProvider('provideVerifyCases')]
    public function testVerify(
        string $userId,
        bool $userFound,
        bool $throwException,
        string $expectedType,
        string $expectedMessage
    ): void
    {
        $this->entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($this->userRepository)
        ;

        $this->userRepository
            ->expects($this->any())
            ->method('find')
            ->with($userId)
            ->willReturn($userFound ? $this->user : null)
        ;

        if ($userFound && $throwException) {
            $this->emailConfirmationHandler
                ->expects($this->once())
                ->method('handleEmailConfirmation')
                ->willThrowException($this->createMock(VerifyEmailExceptionInterface::class))
            ;
        } elseif ($userFound) {
            $this->emailConfirmationHandler
                ->expects($this->once())
                ->method('handleEmailConfirmation')
                ->with($this->request, $this->user)
            ;
        } else {
            $this->emailConfirmationHandler
                ->expects($this->never())
                ->method('handleEmailConfirmation')
            ;
        }

        $result = $this->handler->verify($this->request, $userId);

        $this->assertInstanceOf(EmailVerificationResultDTO::class, $result);
        $this->assertEquals($expectedType, $result->type);
        $this->assertEquals($expectedMessage, $result->message);
    }

    public static function provideVerifyCases(): array
    {
        return [
            'success' => [
                'userId' => '1',
                'userFound' => true,
                'throwException' => false,
                'expectedType' => 'success',
                'expectedMessage' => 'Votre adresse email est confirmée et vous êtes maintenant connecté !',
            ],
            'user not found' => [
                'userId' => 'userIdNotFound',
                'userFound' => false,
                'throwException' => false,
                'expectedType' => 'error',
                'expectedMessage' => 'Utilisateur introuvable.',
            ],
            'expired link' => [
                'userId' => '123',
                'userFound' => true,
                'throwException' => true,
                'expectedType' => 'error',
                'expectedMessage' => 'Le lien de confirmation est invalide ou expiré.',
            ],
        ];
    }
}
