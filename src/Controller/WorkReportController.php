<?php

namespace App\Controller;

use App\DTO\SubmitWorkReportCommandDTO;
use App\Entity\User;
use App\Entity\WorkMonth;
use App\Entity\WorkReportSubmission;
use App\Exception\SubmissionException;
use App\Form\WorkReportSubmissionType;
use App\Service\File\TemporaryFileCleaner;
use App\Service\Submission\WorkReportSubmissionHandler;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/work_report')]
final class WorkReportController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly WorkReportSubmissionHandler $submissionHandler,
        private readonly TemporaryFileCleaner $temporaryFileCleaner,
    ) {}

    #[Route('/submit/{id}', name: 'app_work_report_submit')]
    public function submit(WorkMonth $workMonth, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($workMonth->getUser() !== $user) {
            throw $this->createNotFoundException('Vous n\'êtes pas autorisé à effectuer cet envoi.');
        }

        $workReportSubmission = new WorkReportSubmission();
        $form = $this->createForm(WorkReportSubmissionType::class, $workReportSubmission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submitWorkReportCommandDTO = new SubmitWorkReportCommandDTO(
                $workMonth,
                $workReportSubmission,
                $user,
                $request->request->get('signatureData'),
            );

            try {
                $this->submissionHandler->handler($submitWorkReportCommandDTO);

                if ($this->getParameter('modeDemo')) {
                    $this->addFlash('success', 'Le rapport de travail a été traité et enregistré. (Mode démo : l\'envoi d\'email est désactivé.)');
                } else {
                    $this->addFlash('success', 'Le rapport de travail a été envoyé avec succès.');
                }

                if ($request->isXmlHttpRequest() || $request->headers->get('Turbo-Frame')) {
                    return new Response('<turbo-stream action="replace" target="work-report-form-frame"><template>
                        <script>window.location.reload();</script>
                        </template></turbo-stream>', 200, ['Content-Type' => 'text/vnd.turbo-stream.html']
                    );
                }

                return $this->redirectToRoute('app_home');
            } catch (SubmissionException $exception) {
                $this->logger->error('Erreur lors du processus de soumission : ' . $exception->getMessage(), [
                    'submissionId' => $workReportSubmission->getId(),
                ]);

                throw $exception;
            } finally {
                $this->temporaryFileCleaner->clean(scandir(sys_get_temp_dir() . '/timeboard'));
            }
        }

        return $this->render('work_report/form.html.twig', [
            'form' => $form,
            'workMonth' => $workMonth,
        ]);
    }
}
