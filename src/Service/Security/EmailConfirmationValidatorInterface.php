<?php

namespace App\Service\Security;

use Symfony\Component\HttpFoundation\Request;

interface EmailConfirmationValidatorInterface
{
    public function validate(Request $request, string $userId, string $email): void;
}
