<?php

namespace App\Tests\Service;

use App\Entity\WorkMonth;
use App\Entity\WorkReportSubmission;
use App\Exception\SubmissionException;
use App\Service\Export\TimeSheetPdfGeneratorInterface;
use App\Service\Helper\AttachmentCleaner;
use App\Service\Mailer\WorkReportEmailBuilder;
use App\Service\Submission\WorkReportSubmissionUpdater;
use App\Service\WorkReportMailer;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class WorkReportMailerTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testSendSuccess(): void
    {
        $month = $this->createMock(WorkMonth::class);
        $submission = new WorkReportSubmission();

        $pdfGen = $this->createMock(TimeSheetPdfGeneratorInterface::class);
        $pdfGen->method('generate')->willReturn('/tmp/test.pdf');

        $updater = $this->createMock(WorkReportSubmissionUpdater::class);
        $updater->expects($this->once())->method('prepare');
        $updater->expects($this->once())->method('markSent');

        $emailBuilder = $this->createMock(WorkReportEmailBuilder::class);
        $emailBuilder->method('build')->willReturn(new Email());

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())->method('send');

        $cleaner = $this->createMock(AttachmentCleaner::class);
        $cleaner->expects($this->once())->method('cleanup');

        $logger = $this->createMock(LoggerInterface::class);

        $service = new WorkReportMailer($pdfGen, $updater, $emailBuilder, $mailer, $cleaner, $logger);
        $result = $service->send($month, $submission);

        $this->assertSame($submission, $result);
    }

    /**
     * @throws Exception
     */
    public function testSendFailsOnMailer(): void
    {
        $this->expectException(SubmissionException::class);

        $month = $this->createMock(WorkMonth::class);
        $submission = new WorkReportSubmission();

        $pdfGen = $this->createMock(TimeSheetPdfGeneratorInterface::class);
        $pdfGen->method('generate')->willReturn('/tmp/test.pdf');

        $updater = $this->createMock(WorkReportSubmissionUpdater::class);
        $updater->method('prepare');
        $updater->expects($this->once())->method('markFailed');

        $emailBuilder = $this->createMock(WorkReportEmailBuilder::class);
        $emailBuilder->method('build')->willReturn(new Email());

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->method('send')->willThrowException(new \RuntimeException('error'));

        $cleaner = $this->createMock(AttachmentCleaner::class);
        $logger = $this->createMock(LoggerInterface::class);

        $service = new WorkReportMailer($pdfGen, $updater, $emailBuilder, $mailer, $cleaner, $logger);
        $service->send($month, $submission);
    }
}
