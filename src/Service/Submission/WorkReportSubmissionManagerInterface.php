<?php

namespace App\Service\Submission;

use App\Entity\WorkMonth;
use App\Entity\WorkReportSubmission;

interface WorkReportSubmissionManagerInterface
{
    public function markAsPending(WorkReportSubmission $workReportSubmission, WorkMonth $workMonth, string $pdfPath);
    public function markAsSent(WorkReportSubmission $workReportSubmission);
    public function markAsFailed(WorkReportSubmission $workReportSubmission, \Throwable $exception);
}
