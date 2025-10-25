<?php

namespace App\Service\Submission;

use App\DTO\MailSendContextDTO;
use App\DTO\SubmitWorkReportCommandDTO;
use App\Entity\User;
use App\Entity\WorkMonth;
use App\Entity\WorkReportSubmission;
use App\Exception\SignatureProcessingException;
use App\Exception\SubmissionException;
use App\Service\Export\Section\SheetContext;
use App\Service\Export\TimeSheetExporter;
use App\Service\Formatter\SignatureProcessor;
use App\Service\Mailer\WorkReportMailer;

readonly class WorkReportSubmissionHandler
{
    public function __construct(
        private SheetContext $sheetContext,
        private TimeSheetExporter $exporter,
        private WorkReportSubmissionManagerInterface $workReportSubmissionManager,
        private WorkReportMailer $workReportMailer,
        private SignatureProcessor $signatureProcessor,
    )
    {}

    public function handler(SubmitWorkReportCommandDTO $submitWorkReportCommandDTO): void
    {
        $workMonth = $submitWorkReportCommandDTO->workMonth;
        $workReportSubmission = $submitWorkReportCommandDTO->workReportSubmission;

        $this->processSignature($submitWorkReportCommandDTO->signatureBase64);
        $pdfPath = $this->exporter->export($workMonth);

        $this->workReportSubmissionManager->markAsPending($workReportSubmission, $workMonth, $pdfPath);
        $this->sendReportAndPersistStatus($workReportSubmission, $submitWorkReportCommandDTO->user, $workMonth, $pdfPath);
    }

    private function processSignature(string $signatureBase64): void
    {
        try {
            $tmpSignaturePath = $this->signatureProcessor->process($signatureBase64);
            $this->sheetContext->setSignatureData($tmpSignaturePath);
        } catch(SignatureProcessingException $exception) {
            throw new SubmissionException($exception->getMessage());
        }
    }

    private function sendReportAndPersistStatus(
        WorkReportSubmission $workReportSubmission,
        User $user,
        WorkMonth $workMonth,
        string $pdfPath
    ): void
    {
        $mailSendContextDTO = new MailSendContextDTO(
            $workReportSubmission,
            $user,
            $workMonth,
            $pdfPath,
        );

        try {
            $this->workReportMailer->send($mailSendContextDTO);

            $this->workReportSubmissionManager->markAsSent($workReportSubmission);
        } catch (SubmissionException $exception) {
            $this->workReportSubmissionManager->markAsFailed($workReportSubmission, $exception);

            throw new SubmissionException('Erreur lors du processus de soumission du rapport.', 0, $exception);
        }
    }
}
