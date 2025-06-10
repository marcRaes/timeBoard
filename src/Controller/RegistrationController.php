<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationForm;
use App\Service\EmailConfirmationManager;
use App\Service\RegistrationManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly RegistrationManager $registrationManager,
        private readonly EmailConfirmationManager $emailConfirmationManager,
        private readonly EntityManagerInterface $entityManager,
    )
    {}

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            try {
                $this->registrationManager->register($user, $plainPassword);
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
            $this->emailConfirmationManager->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('error', 'Le lien de confirmation est invalide ou expiré.');

            return $this->redirectToRoute('app_login');
        }

        $this->addFlash('success', 'Votre adresse email est confirmée et vous êtes maintenant connecté !');

        return $this->redirectToRoute('app_home');
    }
}
