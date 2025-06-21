<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class UniqueUserEmail extends Constraint
{
    public string $message = 'Un compte existe déjà avec cette adresse email.';
}
