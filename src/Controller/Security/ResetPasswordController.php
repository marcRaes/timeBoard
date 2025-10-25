<?php

namespace App\Controller\Security;

use App\DTO\PasswordInputDTO;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use App\Service\Security\ResetPasswordMailer;
use App\Service\Token\ResetPassword\ResetPasswordTokenHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class ResetPasswordController extends AbstractController
{
    public function __construct(
        private readonly UserRepository              $userRepository,
        private readonly ResetPasswordTokenHandler   $resetPasswordTokenHandler,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ResetPasswordMailer         $resetPasswordMailer,
    ) {}

    #[Route('/reset-password', name: 'app_forgot_password')]
    public function request(Request $request,): Response
    {
        $email = $request->request->get('email');

        if ($request->isMethod('POST') && $email) {
            $user = $this->userRepository->findOneBy(['email' => $email]);

            if ($user) {
                $token = $this->resetPasswordTokenHandler->generate($user);
                $this->resetPasswordMailer->sendResetEmail($user, $token);
            }

            $this->addFlash('success', 'Si cet email est connu, un lien de réinitialisation a été envoyé.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/forgot_password.html.twig');
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function reset(string $token, Request $request): RedirectResponse|Response
    {
        $user = $this->resetPasswordTokenHandler->validate($token);

        if (!$user) {
            $this->addFlash('danger', 'Le lien de réinitialisation est invalide ou expiré.');

            return $this->redirectToRoute('app_forgot_password');
        }

        $dto = new PasswordInputDTO();
        $form = $this->createForm(ResetPasswordType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $dto->password));
            $this->resetPasswordTokenHandler->invalidate($user);

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'form' => $form
        ]);
    }
}
