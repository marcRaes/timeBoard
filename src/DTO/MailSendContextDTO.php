<?php

namespace App\DTO;

use App\Entity\User;
use App\Entity\WorkMonth;
use App\Entity\WorkReportSubmission;

readonly class MailSendContextDTO
{
    public function __construct(
        public WorkReportSubmission $workReportSubmission,
        public User $user,
        public WorkMonth $workMonth,
        public string $pdfPath
    ) {}
}
