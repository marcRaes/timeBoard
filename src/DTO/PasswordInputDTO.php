<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class PasswordInputDTO
{
    #[Assert\NotBlank(message: "Veuillez entrer un mot de passe.")]
    #[Assert\Length(
        min: 6,
        max: 4096,
        minMessage: "Votre mot de passe doit comporter au moins {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: '/(?=.*[A-Z])(?=.*[a-z])(?=.*\d)/',
        message: "Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre."
    )]
    public string $password = '';
}
