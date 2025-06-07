<?php

namespace App\Service;

use App\Entity\WorkMonth;
use App\Entity\WorkReportSubmission;
use App\Exception\InvalidAttachmentException;
use App\Exception\PdfGenerationException;
use App\Exception\SubmissionException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\File;
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
        private MonthNameHelper $monthNameHelper,
    ) {}

    /**
     * @throws \Throwable
     */
    public function send(WorkMonth $workMonth, WorkReportSubmission $workReportSubmission): WorkReportSubmission
    {
        $pdfFullPath = $this->generateTimesheetPdf($workMonth);

        $this->updateSubmission($workReportSubmission, $workMonth, $pdfFullPath);

        $email = $this->buildEmail($workMonth, $workReportSubmission, $pdfFullPath);

        try {
            $this->mailer->send($email);
            $workReportSubmission->setStatus(WorkReportSubmission::STATUS_SENT);
        } catch (\Throwable $exception) {
            $this->logger->error('Erreur lors de l’envoi du mail : ' . $exception->getMessage(), [
                'submissionId' => $workReportSubmission->getId(),
            ]);
            $workReportSubmission
                ->setStatus(WorkReportSubmission::STATUS_FAILED)
                ->setErrorMessage($exception->getMessage());

            throw new SubmissionException('Erreur lors de la création du PDF. Veuillez réessayer plus tard.', 0, $exception);
        }

        $this->entityManager->flush();
        $this->cleanupAttachment($workReportSubmission);

        return $workReportSubmission;
    }

    /**
     * @throws \Throwable
     */
    private function generateTimesheetPdf(WorkMonth $workMonth): string
    {
        try {
            return $this->timeSheetExporter->create($workMonth);
        } catch (\Throwable $exception) {
            $this->logger->error('Erreur lors de la création du PDF : ' . $exception->getMessage(), [
                'workMonthId' => $workMonth->getId(),
            ]);
            throw new PdfGenerationException('Erreur lors de la création du PDF. Veuillez réessayer plus tard.', 0, $exception);
        }
    }

    private function updateSubmission(WorkReportSubmission $submission, WorkMonth $workMonth, string $pdfFullPath): void
    {
        $submission
            ->setWorkMonth($workMonth)
            ->setSentOn(new \DateTimeImmutable())
            ->setPdfPath($pdfFullPath)
            ->setStatus(WorkReportSubmission::STATUS_PENDING)
            ->setErrorMessage(null);

        $this->entityManager->persist($submission);
        $this->entityManager->flush();
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    private function buildEmail(WorkMonth $workMonth, WorkReportSubmission $submission, string $pdfFullPath): Email
    {
        $attachmentPath = $submission->getAttachmentPath();
        $email = (new Email())
            ->from($workMonth->getUser()->getEmail())
            ->to($submission->getRecipientEmail())
            ->subject(sprintf(
                $attachmentPath !== null ? 'Fiche heure + justificatif transport %s %d' : 'Fiche heure %s %d',
                $this->monthNameHelper->getLocalizedMonthName($workMonth->getMonth()),
                $workMonth->getYear()
            ))
            ->html($this->twig->render('emails/work_report.html.twig', [
                'workMonth'  => $workMonth,
                'submission' => $submission,
            ]));

        $email->attachFromPath($pdfFullPath, basename($pdfFullPath), 'application/pdf');

        if ($attachmentPath !== null) {
            $displayName = $this->getAttachmentFileName($workMonth, $attachmentPath);
            $email->attachFromPath($attachmentPath, $displayName);
        }

        return $email;
    }

    private function getAttachmentFileName(WorkMonth $workMonth, string $attachmentPath): string
    {
        if (!is_file($attachmentPath)) {
            throw new InvalidAttachmentException('Le fichier de justificatif de transport est introuvable.');
        }

        $file = new File($attachmentPath);
        $extension = $file->guessExtension();

        if (!$extension) {
            throw new InvalidAttachmentException('Le justificatif de transport est invalide ou son format n’est pas reconnu.');
        }

        return sprintf(
            'justificatif_transport_%s_%d.%s',
            $this->slugify($this->monthNameHelper->getLocalizedMonthName($workMonth->getMonth())),
            $workMonth->getYear(),
            $extension
        );
    }

    private function slugify(string $text): string
    {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        $text = preg_replace('~[^\\pL\d]+~u', '_', $text);
        $text = strtolower($text);
        return trim($text, '_');
    }

    private function cleanupAttachment(WorkReportSubmission $submission): void
    {
        $attachmentPath = $submission->getAttachmentPath();
        if ($attachmentPath !== null && is_file($attachmentPath)) {
            @unlink($attachmentPath);
        }
    }
}
