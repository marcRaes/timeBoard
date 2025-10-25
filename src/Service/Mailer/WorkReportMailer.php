<?php

namespace App\Service\Mailer;

use App\DTO\MailSendContextDTO;
use App\Exception\MailPreparationException;
use App\Exception\SubmissionException;
use App\Service\Attachment\TransportProofAttachmentHandler;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class WorkReportMailer
{
    public function __construct(
        private MailerInterface $mailer,
        private TransportProofAttachmentHandler $transportProofAttachmentHandler,
        private WorkReportEmailContentBuilder $workReportEmailContentBuilder,
    )
    {}

    public function send(MailSendContextDTO $mailSendContextDTO): void
    {
        try {
            $emailContentDTO = $this->workReportEmailContentBuilder->buildContent(
                $mailSendContextDTO->workReportSubmission,
                $mailSendContextDTO->workMonth,
                $mailSendContextDTO->pdfPath
            );
            $transportProofDTO = $this->transportProofAttachmentHandler->prepare($mailSendContextDTO->workMonth, $emailContentDTO->transportProof);
            $email = (new Email())
                ->from($mailSendContextDTO->user->getEmail())
                ->to($emailContentDTO->recipientEmail)
                ->subject($emailContentDTO->subject)
                ->html($emailContentDTO->htmlBody)
                ->attachFromPath($emailContentDTO->attachmentPath, basename($emailContentDTO->attachmentPath), 'application/pdf');

            if ($transportProofDTO !== null) {
                $email->attachFromPath(
                    $transportProofDTO->path,
                    $transportProofDTO->filename,
                    $transportProofDTO->mimeType
                );
            }

            $this->mailer->send($email);
        } catch (Throwable $exception) {
            if ($exception instanceof TransportExceptionInterface
                || $exception instanceof LoaderError
                || $exception instanceof SyntaxError
                || $exception instanceof RuntimeError
            ) {
                throw new MailPreparationException('Erreur lors de la préparation ou de l\'envoi de l\'email.', 0, $exception);
            }

            if (!$exception instanceof SubmissionException) {
                throw new MailPreparationException('Erreur inattendue lors de l\'envoi du mail.', 0, $exception);
            }

            throw $exception;
        }
    }
}
