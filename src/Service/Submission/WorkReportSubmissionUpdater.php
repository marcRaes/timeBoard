<?php

namespace App\Service\Submission;

use App\Entity\WorkMonth;
use App\Entity\WorkReportSubmission;
use Doctrine\ORM\EntityManagerInterface;

readonly class WorkReportSubmissionUpdater
{
    public function __construct(private EntityManagerInterface $em) {}

    public function prepare(WorkReportSubmission $submission, WorkMonth $month, string $pdfPath): void
    {
        $submission
            ->setWorkMonth($month)
            ->setSentOn(new \DateTimeImmutable())
            ->setPdfPath($pdfPath)
            ->setStatus(WorkReportSubmission::STATUS_PENDING)
            ->setErrorMessage(null);

        $this->em->persist($submission);
        $this->em->flush();
    }

    public function markSent(WorkReportSubmission $submission): void
    {
        $submission->setStatus(WorkReportSubmission::STATUS_SENT);
        $this->em->flush();
    }

    public function markFailed(WorkReportSubmission $submission, \Throwable $e): void
    {
        $submission
            ->setStatus(WorkReportSubmission::STATUS_FAILED)
            ->setErrorMessage($e->getMessage());

        $this->em->flush();
    }
}
