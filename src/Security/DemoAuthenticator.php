<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class DemoAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    public function supports(Request $request): ?bool
    {
        return !$request->getUser() && !$request->attributes->get('_security_passport');
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        /** @var User $user */
        $user = $this->userRepository->findOneBy(['email' => 'demo@timeboard.fr']);

        return new SelfValidatingPassport(
            new UserBadge($user->getEmail(), fn() => $user)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return null;
    }
}
