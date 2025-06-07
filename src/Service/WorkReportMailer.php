<?php

namespace App\Service;

use App\Entity\WorkMonth;
use App\Entity\WorkReportSubmission;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment as TwigEnvironment;
use Psr\Log\LoggerInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class WorkReportMailer
{
    public function __construct(
        private TimeSheetExporter $timeSheetExporter,
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private TwigEnvironment $twig,
        private LoggerInterface $logger,
    ) {}

    /**
     * @throws \Throwable
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function send(WorkMonth $workMonth, WorkReportSubmission $workReportSubmission): WorkReportSubmission
    {
        try {
            $pdfFullPath = $this->timeSheetExporter->create($workMonth);
        } catch (\Throwable $exception) {
            $this->logger->error('Erreur lors de la création du PDF : ' . $exception->getMessage(), [
                'workMonthId' => $workMonth->getId(),
            ]);
            throw $exception;
        }

        $workReportSubmission
            ->setWorkMonth($workMonth)
            ->setSentOn(new \DateTimeImmutable())
            ->setPdfPath($pdfFullPath)
            ->setStatus(WorkReportSubmission::STATUS_PENDING)
            ->setErrorMessage(null);

        $this->entityManager->persist($workReportSubmission);
        $this->entityManager->flush();

        $email = (new Email())
            ->from($workMonth->getUser()->getEmail())
            ->to($workReportSubmission->getRecipientEmail())
            ->subject(sprintf('Rapport de travail %02d/%d', $workMonth->getMonth(), $workMonth->getYear()))
            ->html($this->twig->render('emails/work_report.html.twig', [
                'workMonth'  => $workMonth,
                'submission' => $workReportSubmission,
            ]));

        $email->attachFromPath($pdfFullPath, basename($pdfFullPath), 'application/pdf');

        if ($workReportSubmission->getAttachmentPath() !== null) {
            $email->attachFromPath($workReportSubmission->getAttachmentPath());
        }

        try {
            $this->mailer->send($email);
            $workReportSubmission->setStatus(WorkReportSubmission::STATUS_SENT);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur lors de l’envoi du mail : ' . $e->getMessage(), [
                'submissionId' => $workReportSubmission->getId(),
            ]);
            $workReportSubmission
                ->setStatus(WorkReportSubmission::STATUS_FAILED)
                ->setErrorMessage($e->getMessage());
        }

        $this->entityManager->flush();

        return $workReportSubmission;
    }
}
