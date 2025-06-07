<?php

namespace App\Controller;

use App\Entity\WorkMonth;
use App\Entity\WorkReportSubmission;
use App\Exception\InvalidAttachmentException;
use App\Exception\WorkReportException;
use App\Form\WorkReportSubmissionType;
use App\Service\WorkReportMailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[IsGranted('ROLE_USER')]
#[Route('/work_report')]
final class WorkReportController extends AbstractController
{
    public function __construct(
        private readonly WorkReportMailer $reportMailer,
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
            try {
                $this->reportMailer->send(
                    $workMonth,
                    $workReportSubmission
                );

                $this->addFlash('success', 'Le rapport de travail a été envoyé avec succès.');

                if ($request->isXmlHttpRequest() || $request->headers->get('Turbo-Frame')) {
                    return new Response('<turbo-stream action="replace" target="work-report-form-frame"><template>
                        <script>window.location.reload();</script>
                        </template></turbo-stream>', 200, ['Content-Type' => 'text/vnd.turbo-stream.html']
                    );
                }

                return $this->redirectToRoute('app_home');
            } catch (WorkReportException $exception) {
                $field = $exception->getField();
                if ($field) {
                    $form->get($field)->addError(new FormError($exception->getUserMessage()));
                } else {
                    $form->addError(new FormError($exception->getUserMessage()));
                }
            }
        }

        return $this->render('work_report/form.html.twig', [
            'form' => $form,
            'workMonth' => $workMonth,
        ]);
    }
}
