<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\WorkDay;
use App\Entity\WorkMonth;
use App\Form\WorkDayType;
use App\Service\WorkDayManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_USER')]
final class WorkDayController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WorkDayManager $workDayManager,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly ValidatorInterface $validator
    ) {}

    #[Route('/work-day/create', name: 'work_day_create', methods: ['GET', 'POST'])]
    public function create(Request $request, #[CurrentUser] User $user): Response
    {
        $workDay = $this->workDayManager->initializeCreate();
        $form = $this->createForm(WorkDayType::class, $workDay);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $workMonth = $this->workDayManager->initializeWorkMonth($user, $form->getData());
            $form->getData()->setWorkMonth($workMonth);

            $violations = $this->validator->validate($form->getData());

            if (count($violations) === 0 && $form->isValid()) {
                $this->workDayManager->cleanPeriodsInvalid($form->getData());
                $this->workDayManager->save($form->getData(), $workMonth);

                $this->addFlash('success', 'Journée ajoutée avec succès.');

                return $this->redirectToRoute('app_home');
            }

            foreach ($violations as $violation) {
                $form->get($violation->getPropertyPath())?->addError(new FormError($violation->getMessage()));
            }
        }

        return $this->render('work_day/form.html.twig', [
            'workDayForm' => $form,
            'isNew' => true,
        ]);
    }

    #[Route('/work-day/update/{id}', name: 'work_day_update', methods: ['GET', 'POST'])]
    public function update(WorkDay $workDay, Request $request, #[CurrentUser] User $user): Response
    {
        $form = $this->createForm(WorkDayType::class, $workDay);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $workMonth = $this->workDayManager->initializeWorkMonth($user, $form->getData());
            $form->getData()->setWorkMonth($workMonth);

            $violations = $this->validator->validate($form->getData());

            if (count($violations) === 0 && $form->isValid()) {
                $this->workDayManager->cleanPeriodsInvalid($form->getData());
                $this->workDayManager->save($form->getData(), $workMonth);

                $this->addFlash('success', 'Journée modifiée avec succès.');

                return $this->redirectToRoute('work_day_show', [
                    'id' => $workMonth->getId(),
                ]);
            }

            foreach ($violations as $violation) {
                $form->get($violation->getPropertyPath())?->addError(new FormError($violation->getMessage()));
            }
        }

        return $this->render('work_day/form.html.twig', [
            'workDayForm' => $form,
            'isNew' => false,
        ]);
    }

    #[Route('/work-day/show/{id}', name: 'work_day_show', methods: ['GET', 'POST'])]
    public function show(WorkMonth $workMonth, #[CurrentUser] User $user): Response
    {
        if ($workMonth->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à voir cette journée de travail.');
        }

        return $this->render('work_day/show.html.twig', [
            'workMonth' => $workMonth,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/work-day/delete/{id}', name: 'work_day_delete', methods: ['POST'])]
    public function delete(WorkDay $workDay, Request $request, #[CurrentUser] User $user): RedirectResponse
    {
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('delete'.$workDay->getId(), $request->get('_token')))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $workMonth = $workDay->getWorkMonth();

        if ($workMonth->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer cette journée de travail.');
        }

        foreach($workDay->getWorkPeriods() as $period) {
            $this->entityManager->remove($period);
        }

        $this->entityManager->remove($workDay);
        $this->entityManager->flush();

        if ($workMonth->getWorkDays()->count() === 0) {
            $this->entityManager->remove($workMonth);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        $this->addFlash('success', 'Journée supprimée avec succès.');

        return $this->redirectToRoute('work_day_show', [
            'id' => $workDay->getWorkMonth()->getId(),
        ]);
    }
}
