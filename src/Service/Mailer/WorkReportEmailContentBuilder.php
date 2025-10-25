<?php

namespace App\Service\Mailer;

use App\DTO\EmailContentDTO;
use App\Entity\WorkMonth;
use App\Entity\WorkReportSubmission;
use App\Service\Formatter\MonthNameFormatter;
use App\Service\Formatter\SlugGenerator;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class WorkReportEmailContentBuilder
{
    public function __construct(
        private SlugGenerator $slugGenerator,
        private MonthNameFormatter $monthNameFormatter,
        private Environment $twig,
    ){}

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function buildContent(WorkReportSubmission $workReportSubmission, WorkMonth $workMonth, string $pdfPath): EmailContentDTO
    {
        $year = $workMonth->getYear();
        $monthSlug = $this->slugGenerator->slugify($this->monthNameFormatter->getLocalizedMonthName($workMonth->getMonth()));
        $transportProof = $workReportSubmission->getAttachmentPath();
        $subject = $transportProof
            ? sprintf('Fiche heure + justificatif transport %s %d', $monthSlug, $year)
            : sprintf('Fiche heure %s %d', $monthSlug, $year);
        $htmlBody = $this->twig->render('emails/work_report.html.twig', [
            'workMonth' => $workMonth,
            'submission' => $workReportSubmission
        ]);

        return new EmailContentDTO(
            $subject,
            $htmlBody,
            $workReportSubmission->getRecipientEmail(),
            $pdfPath,
            $transportProof
        );
    }
}
