<?php

namespace App\Tests\Service\Submission;

use App\Entity\WorkMonth;
use App\Entity\WorkReportSubmission;
use App\Service\Submission\WorkReportSubmissionUpdater;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class WorkReportSubmissionUpdaterTest extends TestCase
{
    private WorkMonth $workMonth;
    private EntityManagerInterface $entityManager;
    private WorkReportSubmission $submission;
    private WorkReportSubmissionUpdater $submissionUpdater;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->workMonth = $this->createStub(WorkMonth::class);
        $this->submission = new WorkReportSubmission();
        $this->submissionUpdater = new WorkReportSubmissionUpdater($this->entityManager);
    }

    /**
     * @throws Exception
     */
    public function testPrepareSetsInitialValues(): void
    {
        $this->entityManager->expects($this->once())->method('persist')->with($this->submission);
        $this->entityManager->expects($this->once())->method('flush');
        $this->submissionUpdater->prepare($this->submission, $this->workMonth, '/test.pdf');

        $this->assertSame('/test.pdf', $this->submission->getPdfPath());
        $this->assertSame(WorkReportSubmission::STATUS_PENDING, $this->submission->getStatus());
        $this->assertLessThan(5, time() - $this->submission->getSentOn()->getTimestamp());
        $this->assertInstanceOf(DateTimeImmutable::class, $this->submission->getSentOn());
        $this->assertNull($this->submission->getErrorMessage());
    }

    /**
     * @throws Exception
     */
    public function testMarkSentUpdatesStatus(): void
    {
        $this->entityManager->expects($this->once())->method('flush');

        $this->submissionUpdater->markSent($this->submission);
        $this->assertSame(WorkReportSubmission::STATUS_SENT, $this->submission->getStatus());
    }

    /**
     * @throws Exception
     */
    public function testMarkFailedUpdatesStatusAndMessage(): void
    {
        $this->entityManager->expects($this->once())->method('flush');

        $exception = new \Exception('Oops');

        $this->submissionUpdater->markFailed($this->submission, $exception);
        $this->assertSame(WorkReportSubmission::STATUS_FAILED, $this->submission->getStatus());
        $this->assertSame('Oops', $this->submission->getErrorMessage());
    }
}
