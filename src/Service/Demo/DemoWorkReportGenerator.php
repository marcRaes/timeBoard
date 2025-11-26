<?php

namespace App\Service\Demo;

use App\Entity\WorkMonth;
use App\Entity\WorkReportSubmission;
use App\Service\Export\TimeSheetExporter;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;

readonly class DemoWorkReportGenerator
{
    public function __construct(
        private TimeSheetExporter $exporter,
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * @throws RandomException
     */
    public function generateSubmission(WorkMonth $workMonth, string $recipientEmail): WorkReportSubmission
    {
        $pdfPath = $this->exporter->export($workMonth);

        $year  = $workMonth->getYear();
        $month = $workMonth->getMonth();

        $startDay = new \DateTimeImmutable("{$year}-{$month}-26");
        $endDay   = (new \DateTimeImmutable("{$year}-{$month}-01"))->modify('last day of this month');

        $dayOffset = random_int(0, $endDay->format('d') - 26);
        $sentOn = $startDay->modify("+{$dayOffset} days");

        $hour = random_int(8, 22);
        $minute = random_int(0, 59);

        $sentOn = $sentOn->setTime($hour, $minute);

        $submission = new WorkReportSubmission();
        $submission->setWorkMonth($workMonth);
        $submission->setSentOn($sentOn);
        $submission->setRecipientEmail($recipientEmail);
        $submission->setPdfPath($pdfPath);
        $submission->setAttachmentPath(null);
        $submission->setStatus(WorkReportSubmission::STATUS_SENT);

        $this->entityManager->persist($submission);

        return $submission;
    }
}
