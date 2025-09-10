<?php

namespace App\Tests\Factory;

use App\Dto\PasswordInputDto;
use App\Dto\RegistrationDto;
use App\Entity\User;
use App\Factory\UserFactory;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFactoryTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testFromDtoCreatesUserCorrectly(): void
    {
        $dto = new RegistrationDto();
        $dto->firstName = 'Alice';
        $dto->lastName = 'Dupont';
        $dto->email = 'alice@example.com';
        $dto->password = new PasswordInputDto();
        $dto->password->password = 'P@ssw0rd';

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->with($this->isInstanceOf(User::class), 'P@ssw0rd')
            ->willReturn('hashed_password');

        $factory = new UserFactory($passwordHasher);

        $user = $factory->fromDto($dto);

        $this->assertSame('Alice', $user->getFirstName());
        $this->assertSame('Dupont', $user->getLastName());
        $this->assertSame('alice@example.com', $user->getEmail());
        $this->assertSame('hashed_password', $user->getPassword());
    }
}
