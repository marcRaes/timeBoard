<?php

namespace App\DTO;

use App\Entity\User;
use App\Entity\WorkMonth;
use App\Entity\WorkReportSubmission;

readonly class SubmitWorkReportCommandDTO
{
    public function __construct(
        public WorkMonth $workMonth,
        public WorkReportSubmission $workReportSubmission,
        public User $user,
        public string $signatureBase64,
    ) {}
}
