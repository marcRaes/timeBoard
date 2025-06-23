<?php

namespace App\Service;

use App\Entity\WorkMonth;
use App\Entity\WorkReportSubmission;
use App\Exception\SubmissionException;
use App\Service\Export\TimeSheetPdfGeneratorInterface;
use App\Service\Helper\AttachmentCleaner;
use App\Service\Mailer\WorkReportEmailBuilder;
use App\Service\Submission\WorkReportSubmissionUpdater;
use Symfony\Component\Mailer\MailerInterface;
use Psr\Log\LoggerInterface;

readonly class WorkReportMailer
{
    public function __construct(
        private TimeSheetPdfGeneratorInterface $pdfGenerator,
        private WorkReportSubmissionUpdater $submissionUpdater,
        private WorkReportEmailBuilder $emailBuilder,
        private MailerInterface $mailer,
        private AttachmentCleaner $attachmentCleaner,
        private LoggerInterface $logger
    ) {}

    public function send(WorkMonth $month, WorkReportSubmission $submission): WorkReportSubmission
    {
        $pdfPath = $this->pdfGenerator->generate($month);

        $this->submissionUpdater->prepare($submission, $month, $pdfPath);

        try {
            $email = $this->emailBuilder->build($month, $submission, $pdfPath);
            $this->mailer->send($email);
            $this->submissionUpdater->markSent($submission);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur envoi email : ' . $e->getMessage(), [
                'submissionId' => $submission->getId(),
            ]);
            $this->submissionUpdater->markFailed($submission, $e);
            throw new SubmissionException('Erreur lors de l’envoi du rapport.', 0, $e);
        }

        $this->attachmentCleaner->cleanup($submission->getAttachmentPath());

        return $submission;
    }
}
