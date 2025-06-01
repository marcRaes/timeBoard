<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\WorkDay;
use App\Entity\WorkMonth;
use App\Entity\WorkPeriod;
use App\Form\WorkDayTypeForm;
use App\Repository\WorkDayRepository;
use App\Repository\WorkMonthRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class WorkDayController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WorkMonthRepository $workMonthRepository,
        private readonly LoggerInterface $logger,
    ) {}

    #[Route('/work-day/{id?}', name: 'work_day_form', methods: ['GET', 'POST'])]
    public function form(?WorkDay $workDay, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $isNew = !$workDay;

        if ($isNew) {
            $workDay = new WorkDay();
            $workDay->setIsFullDay(false);
            $workDay->setHasLunchTicket(false);

            for ($i = 0; $i < 2; $i++) {
                $period = new WorkPeriod();
                $period->setWorkDay($workDay);
                $workDay->addWorkPeriod($period);
            }
        }

        $formData = $request->get('work_day_type_form');

        if ($formData && isset($formData['date']) && $isNew) {
            try {
                $submittedDate = new \DateTimeImmutable($formData['date']);
                $month = (int)$submittedDate->format('m');
                $year = (int)$submittedDate->format('Y');

                $workMonth = $this->workMonthRepository->findOneBy([
                    'user' => $user,
                    'month' => $month,
                    'year' => $year,
                ]);

                if (!$workMonth) {
                    $workMonth = new WorkMonth();
                    $workMonth->setUser($user);
                    $workMonth->setMonth($month);
                    $workMonth->setYear($year);
                }

                $workDay->setDate($submittedDate);
                $workDay->setWorkMonth($workMonth);

            } catch (\Exception $e) {
                $this->logger->error('Date invalide : ' . $e->getMessage());
            }
        }

        $form = $this->createForm(WorkDayTypeForm::class, $workDay);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hasAtLeastOneCompletePeriod = false;

            $workMonth = $this->workMonthRepository->findOneBy([
                'user' => $user,
                'month' => (int) $workDay->getDate()->format('m'),
                'year' => (int) $workDay->getDate()->format('Y'),
            ]);

            if (!$workMonth) {
                $workMonth = new WorkMonth();
                $workMonth->setUser($user);
                $workMonth->setMonth((int)$workDay->getDate()->format('m'));
                $workMonth->setYear((int)$workDay->getDate()->format('Y'));
            }

            $workDay->setWorkMonth($workMonth);

            foreach ($workDay->getWorkPeriods() as $period) {
                if (
                    $period->getTimeStart() &&
                    $period->getTimeEnd() &&
                    $period->getDuration() &&
                    $period->getLocation()
                ) {
                    $hasAtLeastOneCompletePeriod = true;
                }

                if (!$period->getTimeStart()) {
                    $workDay->removeWorkPeriod($period);
                }
            }

            if (count($workDay->getWorkPeriods()) > 4) {
                $form->addError(new FormError('Vous ne pouvez pas avoir plus de 4 créneaux horaires.'));
            }

            if (!$hasAtLeastOneCompletePeriod) {
                $form->addError(new FormError('Veuillez remplir au moins un créneau complet (heure début, heure fin, temps, lieu).'));
            }

            $this->entityManager->persist($workMonth);
            $this->entityManager->persist($workDay);
            $this->entityManager->flush();

            $this->addFlash('success', $isNew ? 'Journée ajoutée avec succès.' : 'Journée modifiée avec succès.');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('work_day/form.html.twig', [
            'workDayForm' => $form,
            'is_editing' => !$isNew,
        ]);
    }

    #[Route('/work-day/show/{id}', name: 'work_day_show', methods: ['GET', 'POST'])]
    public function show(WorkMonth $workMonth): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($workMonth->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à voir cette journée de travail.');
        }

        return $this->render('work_day/show.html.twig', [
            'workMonth' => $workMonth,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/work-day/{id}/delete', name: 'work_day_delete', methods: ['POST'])]
    public function delete(WorkDay $workDay, CsrfTokenManagerInterface $csrfTokenManager, Request $request): RedirectResponse
    {
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('delete'.$workDay->getId(), $request->get('_token')))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($workDay->getWorkMonth()->getUser() !== $user) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer cette journée de travail.');
        }

        $this->entityManager->remove($workDay);
        $this->entityManager->flush();

        $this->addFlash('success', 'Journée supprimée avec succès.');

        return $this->redirectToRoute('app_home');
    }
}
