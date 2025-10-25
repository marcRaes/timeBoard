<?php

namespace App\Service\Submission;

use App\Entity\WorkMonth;
use App\Entity\WorkReportSubmission;
use Doctrine\ORM\EntityManagerInterface;

readonly class WorkReportSubmissionManager implements WorkReportSubmissionManagerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    )
    {}

    public function markAsPending(WorkReportSubmission $workReportSubmission, WorkMonth $workMonth, string $pdfPath):void
    {
        $workReportSubmission
            ->setWorkMonth($workMonth)
            ->setSentOn(new \DateTimeImmutable())
            ->setPdfPath($pdfPath)
            ->setStatus(WorkReportSubmission::STATUS_PENDING)
            ->setErrorMessage(null);

        $this->entityManager->persist($workReportSubmission);
        $this->entityManager->flush();
    }

    public function markAsSent(WorkReportSubmission $workReportSubmission): void
    {
        $workReportSubmission
            ->setStatus(WorkReportSubmission::STATUS_SENT);

        $this->entityManager->flush();
    }

    public function markAsFailed(WorkReportSubmission $workReportSubmission, \Throwable $exception): void
    {
        $workReportSubmission
            ->setStatus(WorkReportSubmission::STATUS_FAILED)
            ->setErrorMessage($exception->getMessage());

        $this->entityManager->flush();
    }
}
