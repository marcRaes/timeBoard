<?php

namespace App\Service\Token\ResetPassword;

use Symfony\Component\Uid\Uuid;

class ResetPasswordTokenGenerator
{
    public function generate(): string
    {
        return Uuid::v4()->toRfc4122();
    }
}
