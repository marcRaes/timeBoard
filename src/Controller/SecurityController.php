<?php

namespace App\Controller;

use App\Dto\PasswordInputDto;
use App\Dto\RegistrationDto;
use App\Entity\User;
use App\Form\RegistrationForm;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use App\Service\EmailConfirmationHandler;
use App\Service\EmailConfirmationSender;
use App\Service\RegistrationManager;
use App\Service\ResetPasswordMailer;
use App\Service\ResetPasswordTokenManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class SecurityController extends AbstractController
{
    public function __construct(
        private readonly RegistrationManager $registrationManager,
        private readonly EmailConfirmationHandler $emailConfirmationHandler,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly ResetPasswordTokenManager $resetPasswordTokenManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ResetPasswordMailer $resetPasswordMailer,
        private readonly LoggerInterface $logger,
        private readonly EmailConfirmationSender $emailConfirmationSender,
        private readonly RateLimiterFactory $emailLimiter
    )
    {}

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($error instanceof CustomUserMessageAccountStatusException) {
            $this->addFlash('danger', $error->getMessage());
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {}

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        $dto = new RegistrationDto();
        $form = $this->createForm(RegistrationForm::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->registrationManager->register($dto);
                $this->logger->info('Inscription réussie, email de confirmation envoyé à '.$dto->email);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l’inscription.');

                return $this->redirectToRoute('app_register');
            }

            return $this->redirectToRoute('app_home');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email/{id}', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, string $id): Response
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        try {
            $this->emailConfirmationHandler->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('error', 'Le lien de confirmation est invalide ou expiré.');

            return $this->redirectToRoute('app_login');
        }

        $this->addFlash('success', 'Votre adresse email est confirmée et vous êtes maintenant connecté !');

        return $this->redirectToRoute('app_home');
    }

    #[Route('/reset-password', name: 'app_forgot_password')]
    public function request(Request $request,): Response
    {
        $email = $request->request->get('email');

        if ($request->isMethod('POST') && $email) {
            $user = $this->userRepository->findOneBy(['email' => $email]);

            if ($user) {
                $token = $this->resetPasswordTokenManager->generate($user);
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
        $user = $this->resetPasswordTokenManager->validate($token);

        if (!$user) {
            $this->addFlash('danger', 'Le lien de réinitialisation est invalide ou expiré.');

            return $this->redirectToRoute('app_forgot_password');
        }

        $dto = new PasswordInputDto();
        $form = $this->createForm(ResetPasswordType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $dto->password));
            $this->resetPasswordTokenManager->invalidate($user);

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'form' => $form
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/resend-confirmation', name: 'app_resend_confirmation', methods: ['POST'])]
    public function resendConfirmationEmail(Request $request): RedirectResponse
    {
        $email = $request->request->get('email');

        if (!$email) {
            $this->addFlash('danger', 'Adresse email requise.');

            return $this->redirectToRoute('app_login');
        }

        $limiter = $this->emailLimiter->create($email);
        $limit = $limiter->consume();

        if (!$limit->isAccepted()) {
            $this->addFlash('warning', 'Vous avez récemment demandé un renvoi. Merci de patienter quelques minutes.');

            return $this->redirectToRoute('app_login');
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if ($user && !$user->isVerified()) {
            $this->emailConfirmationSender->send($user);
            $this->addFlash('success', 'Un nouvel email de confirmation vous a été envoyé.');
        } else {
            $this->addFlash('info', 'Si ce compte existe, un email de confirmation a déjà été envoyé.');
        }

        return $this->redirectToRoute('app_login');
    }
}
