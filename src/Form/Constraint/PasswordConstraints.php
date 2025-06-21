<?php

namespace App\Form\Constraint;

use Symfony\Component\Validator\Constraints as Assert;

class PasswordConstraints
{
    public static function get(): array
    {
        return [
            new Assert\NotBlank([
                'message' => 'Veuillez entrer un mot de passe.',
            ]),
            new Assert\Length([
                'min' => 6,
                'minMessage' => 'Votre mot de passe doit comporter au moins {{ limit }} caractères.',
                'max' => 4096,
            ]),
        ];
    }
}
