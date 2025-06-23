<?php

namespace App\Controller\Security;

use App\Dto\RegistrationDto;
use App\Form\RegistrationForm;
use App\Service\Security\RegistrationHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly RegistrationHandler $registrationHandler
    ) {}

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
                $this->registrationHandler->register($dto);
                $this->addFlash('success', 'Inscription réussie. Un email de confirmation vous a été envoyé.');

                return $this->redirectToRoute('app_home');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l’inscription.');
            }
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
