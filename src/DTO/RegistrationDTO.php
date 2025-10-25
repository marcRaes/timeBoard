<?php

namespace App\DTO;

use App\Validator\UniqueUserEmail;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationDTO
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
    public PasswordInputDTO $password;

    public function __construct()
    {
        $this->password = new PasswordInputDTO();
    }
}
