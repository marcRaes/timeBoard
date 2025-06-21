<?php

namespace App\Validator;

use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueUserEmailValidator extends ConstraintValidator
{
    public function __construct(private readonly UserRepository $userRepository) {}

    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint UniqueUserEmail */

        if (null === $value || '' === $value) {
            return;
        }

        if ($this->userRepository->findOneBy(['email' => $value])) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
