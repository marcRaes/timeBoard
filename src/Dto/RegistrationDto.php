<?php

namespace App\Dto;

use App\Validator\UniqueUserEmail;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationDto
{
    #[Assert\NotBlank(message: "Veuillez entrer votre prénom.")]
    public string $firstName = '';

    #[Assert\NotBlank(message: "Veuillez entrer votre nom.")]
    public string $lastName = '';

    #[Assert\NotBlank(message: "Veuillez entrer votre email.")]
    #[Assert\Email(message: "L'adresse email n'est pas valide.")]
    #[UniqueUserEmail]
    public string $email = '';

    #[Assert\Valid]
    public PasswordInputDto $password;

    public function __construct()
    {
        $this->password = new PasswordInputDto();
    }
}
