<?php

namespace App\Service\Mailer;

use App\Entity\WorkMonth;
use App\Entity\WorkReportSubmission;
use App\Service\Helper\AttachmentHelper;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class WorkReportEmailBuilder
{
    public function __construct(
        private Environment $twig,
        private AttachmentHelper $attachmentHelper
    ) {}

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function build(WorkMonth $month, WorkReportSubmission $submission, string $pdfPath): Email
    {
        $email = (new Email())
            ->from($month->getUser()->getEmail())
            ->to($submission->getRecipientEmail())
            ->subject(sprintf(
                $submission->getAttachmentPath() ? 'Fiche heure + justificatif transport %s %d' : 'Fiche heure %s %d',
                $this->attachmentHelper->getLocalizedMonthSlug($month),
                $month->getYear()
            ))
            ->html($this->twig->render('emails/work_report.html.twig', [
                'workMonth' => $month,
                'submission' => $submission
            ]));

        $email->attachFromPath($pdfPath, basename($pdfPath), 'application/pdf');

        if ($submission->getAttachmentPath()) {
            $filename = $this->attachmentHelper->getAttachmentFileName($month, $submission->getAttachmentPath());
            $email->attachFromPath($submission->getAttachmentPath(), $filename);
        }

        return $email;
    }
}
