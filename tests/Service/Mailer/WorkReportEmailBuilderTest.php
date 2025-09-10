<?php

namespace App\Tests\Service\Mailer;

use App\Entity\User;
use App\Entity\WorkMonth;
use App\Entity\WorkReportSubmission;
use App\Service\Attachment\AttachmentNameGenerator;
use App\Service\Helper\SlugHelper;
use App\Service\Mailer\WorkReportEmailBuilder;
use App\Service\MonthNameHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class WorkReportEmailBuilderTest extends TestCase
{
    /**
     * @throws SyntaxError
     * @throws Exception
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[DataProvider('provideSubmissionCases')]
    public function testBuildReturnsEmailWithOrWithoutAttachment(?string $attachmentPath, string $expectedSubject): void
    {
        $email = $this->createEmailBuilderAndCallBuild($attachmentPath);

        $this->assertSame('from@example.com', $email->getFrom()[0]->getAddress());
        $this->assertSame('to@example.com', $email->getTo()[0]->getAddress());
        $this->assertSame($expectedSubject, $email->getSubject());
    }

    /**
     * @throws SyntaxError
     * @throws Exception
     * @throws RuntimeError
     * @throws LoaderError
     */
    private function createEmailBuilderAndCallBuild(?string $attachmentPath): Email
    {
        $twig = $this->createMock(Environment::class);
        $slugHelper = $this->createMock(SlugHelper::class);
        $monthNameHelper = $this->createMock(MonthNameHelper::class);
        $attachmentNameGenerator = $this->createMock(AttachmentNameGenerator::class);
        $month = $this->createMock(WorkMonth::class);
        $submission = $this->createMock(WorkReportSubmission::class);
        $user = $this->createMock(User::class);

        $user->expects($this->once())
            ->method('getEmail')
            ->willReturn('from@example.com')
        ;

        $month->expects($this->once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $month->expects($this->once())
            ->method('getYear')
            ->willReturn(2024)
        ;

        $month->expects($this->once())
            ->method('getMonth')
            ->willReturn(6)
        ;

        $submission->expects($this->once())
            ->method('getRecipientEmail')
            ->willReturn('to@example.com')
        ;

        $submission->expects($this->once())
            ->method('getAttachmentPath')
            ->willReturn($attachmentPath)
        ;

        $monthNameHelper->expects($this->once())
            ->method('getLocalizedMonthName')
            ->with(6)
            ->willReturn('Juin')
        ;

        $slugHelper->expects($this->once())
            ->method('slugify')
            ->with('Juin')
            ->willReturn('juin')
        ;

        if ($attachmentPath) {
            $attachmentNameGenerator->expects($this->once())
                ->method('generate')
                ->with($month, $attachmentPath)
                ->willReturn('justificatif.pdf')
            ;
        }

        $twig->expects($this->once())
            ->method('render')
            ->willReturn('<p>Contenu HTML</p>')
        ;

        $builder = new WorkReportEmailBuilder($twig, $attachmentNameGenerator, $slugHelper, $monthNameHelper);

        return $builder->build($month, $submission, '/tmp/report.pdf');
    }

    public static function provideSubmissionCases(): array
    {
        return [
            'sans justificatif' => [null, 'Fiche heure juin 2024'],
            'avec justificatif' => ['/tmp/justificatif.pdf', 'Fiche heure + justificatif transport juin 2024'],
        ];
    }
}
