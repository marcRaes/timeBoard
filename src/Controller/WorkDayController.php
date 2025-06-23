<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\WorkDay;
use App\Http\Redirect\WorkDayRedirectResolver;
use App\Http\Turbo\AjaxRedirectResponseFactory;
use App\Service\TimeTracking\WorkDayDeletionRequestHandler;
use App\Service\TimeTracking\WorkDayFormHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/work-day')]
final class WorkDayController extends AbstractController
{
    public function __construct(
        private readonly WorkDayFormHandler $workDayFormHandler,
        private readonly AjaxRedirectResponseFactory $ajaxRedirectResponseFactory,
        private readonly WorkDayDeletionRequestHandler $workDayDeletionRequestHandler,
        private readonly WorkDayRedirectResolver $workDayRedirectResolver
    ) {}

    #[Route('/create', name: 'app_work_day_create', methods: ['GET', 'POST'])]
    public function create(Request $request, #[CurrentUser] User $user): Response
    {
        $form = $this->workDayFormHandler->handle($request, $user);

        if ($this->workDayFormHandler->isSuccess()) {
            $this->addFlash('success', 'Journée ajoutée avec succès.');

            return $this->ajaxRedirectResponseFactory->createRedirectOrTurbo($request, 'app_home', 'work-day-form-new');
        }

        return $this->render('work_day/form.html.twig', [
            'workDayForm' => $form,
            'isNew' => true,
        ]);
    }

    #[Route('/update/{id}', name: 'app_work_day_update', methods: ['GET', 'POST'])]
    public function update(WorkDay $workDay, Request $request, #[CurrentUser] User $user): Response
    {
        $form = $this->workDayFormHandler->handle($request, $user, $workDay);

        if ($this->workDayFormHandler->isSuccess()) {
            $this->addFlash('success', 'Journée modifiée avec succès.');

            return $this->ajaxRedirectResponseFactory->createRedirectOrTurbo(
                $request,
                'work_day_show',
                'work-day-form-' . $workDay->getId(),
                ['id' => $workDay->getWorkMonth()->getId()]
            );
        }

        return $this->render('work_day/form.html.twig', [
            'workDayForm' => $form,
            'workDay' => $workDay,
            'isNew' => false,
        ]);
    }

    #[Route('/partial/{id}', name: 'app_work_day_partial', methods: ['GET'])]
    public function partial(WorkDay $workDay): Response
    {
        return $this->render('work_day/_card.html.twig', [
            'workDay' => $workDay,
            'workMonth' => $workDay->getWorkMonth(),
        ]);
    }

    #[Route('/delete/{id}', name: 'app_work_day_delete', methods: ['POST'])]
    public function delete(WorkDay $workDay, Request $request): RedirectResponse
    {
        $workMonth = $workDay->getWorkMonth();

        $this->workDayDeletionRequestHandler->handle($request, $workDay);
        $this->addFlash('success', 'Journée supprimée avec succès.');

        return $this->workDayRedirectResolver->resolveAfterDelete($workMonth);
    }
}
