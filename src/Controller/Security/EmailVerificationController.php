<?php

namespace App\Controller\Security;

use App\Service\Security\EmailVerificationHandler;
use App\Service\Security\ResendEmailConfirmationHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

class EmailVerificationController extends AbstractController
{
    public function __construct(
        private readonly EmailVerificationHandler $emailVerificationHandler,
        private readonly ResendEmailConfirmationHandler $resendEmailConfirmationHandler,
    ) {}

    #[Route('/verify/email/{id}', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, string $id): Response
    {
        $result = $this->emailVerificationHandler->verify($request, $id);
        $this->addFlash($result->type, $result->message);

        return $this->redirectToRoute($result->redirectRoute);
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/resend-confirmation', name: 'app_resend_confirmation', methods: ['POST'])]
    public function resendConfirmationEmail(Request $request): RedirectResponse
    {
        $result = $this->resendEmailConfirmationHandler->handle($request->request->get('email'));
        $this->addFlash($result->type, $result->message);

        return $this->redirectToRoute('app_login');
    }
}
