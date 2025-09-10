<?php

namespace App\Service\Security;

use Symfony\Component\HttpFoundation\Request;
use SymfonyCasts\Bundle\VerifyEmail\Exception\ExpiredSignatureException;
use SymfonyCasts\Bundle\VerifyEmail\Exception\InvalidSignatureException;
use SymfonyCasts\Bundle\VerifyEmail\Exception\WrongEmailVerifyException;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

final readonly class VerifyEmailHelperAdapter implements EmailConfirmationValidatorInterface
{
    public function __construct(private VerifyEmailHelperInterface $verifyEmailHelper) {}

    /**
     * @throws ExpiredSignatureException
     * @throws WrongEmailVerifyException
     * @throws InvalidSignatureException
     */
    public function validate(Request $request, string $userId, string $email): void
    {
        $this->verifyEmailHelper->validateEmailConfirmationFromRequest($request, $userId, $email);
    }
}
