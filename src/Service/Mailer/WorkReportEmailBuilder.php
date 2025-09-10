<?php

namespace App\Service\Mailer;

use App\Entity\WorkMonth;
use App\Entity\WorkReportSubmission;
use App\Service\Attachment\AttachmentNameGenerator;
use App\Service\Helper\SlugHelper;
use App\Service\MonthNameHelper;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class WorkReportEmailBuilder
{
    public function __construct(
        private Environment $twig,
        private AttachmentNameGenerator $attachmentNameGenerator,
        private SlugHelper $slugHelper,
        private MonthNameHelper $monthNameHelper,
    ) {}

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function build(WorkMonth $month, WorkReportSubmission $submission, string $pdfPath): Email
    {
        $user = $month->getUser();
        $recipient = $submission->getRecipientEmail();
        $attachmentPath = $submission->getAttachmentPath();

        $email = (new Email())
            ->from($user->getEmail())
            ->to($recipient)
            ->subject($this->getSubject($month, $attachmentPath))
            ->html($this->twig->render('emails/work_report.html.twig', [
                'workMonth' => $month,
                'submission' => $submission
            ]));

        $email->attachFromPath($pdfPath, basename($pdfPath), 'application/pdf');

        if ($attachmentPath) {
            $filename = $this->attachmentNameGenerator->generate($month, $attachmentPath);
            $email->attachFromPath($attachmentPath, $filename);
        }

        return $email;
    }

    private function getSubject(WorkMonth $month, ?string $attachmentPath): string
    {
        $year = $month->getYear();
        $monthSlug = $this->slugHelper->slugify($this->monthNameHelper->getLocalizedMonthName($month->getMonth()));

        return $attachmentPath
            ? sprintf('Fiche heure + justificatif transport %s %d', $monthSlug, $year)
            : sprintf('Fiche heure %s %d', $monthSlug, $year);
    }
}
