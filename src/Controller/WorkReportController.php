<?php

namespace App\Controller;

use App\Entity\WorkMonth;
use App\Entity\WorkReportSubmission;
use App\Form\WorkReportSubmissionType;
use App\Service\WorkReportMailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[IsGranted('ROLE_USER')]
#[Route('/work_report')]
final class WorkReportController extends AbstractController
{
    public function __construct(
        private readonly WorkReportMailer $reportMailer,
        private readonly SluggerInterface $slugger
    ) {}

    /**
     * @throws SyntaxError
     * @throws \Throwable
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route('/submit/{id}', name: 'app_work_report_submit')]
    public function submit(WorkMonth $workMonth, Request $request): Response
    {
        if ($workMonth->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Vous n\'êtes pas autorisé à effectuer cet envoi.');
        }

        $workReportSubmission = new WorkReportSubmission();
        $form = $this->createForm(WorkReportSubmissionType::class, $workReportSubmission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->reportMailer->send(
                $workMonth,
                $workReportSubmission
            );

            $this->addFlash('success', 'Le rapport de travail a été envoyé avec succès.');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('work_report/index.html.twig', [
            'form' => $form,
        ]);
    }
}
