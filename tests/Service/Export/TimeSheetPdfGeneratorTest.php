<?php

namespace App\Tests\Service\Export;

use App\Config\TimeSheetConfig;
use App\Entity\User;
use App\Entity\WorkMonth;
use App\Exception\PdfGenerationException;
use App\Service\Export\DirectoryManager;
use App\Service\Export\FileNamer;
use App\Service\Export\SpreadsheetWriterInterface;
use App\Service\Export\TimeSheetBuilder;
use App\Service\Export\TimeSheetExporter;
use App\Service\Export\TimeSheetPdfGenerator;
use App\Service\Helper\SlugHelper;
use App\Service\MonthNameHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class TimeSheetPdfGeneratorTest extends TestCase
{
    private string $tempDir;

    /**
     * @throws Exception
     */
    #[DataProvider('providePdfGenerationData')]
    public function testGenerateReturnsPdfPathWithVariousData(
        string $firstName,
        string $lastName,
        int $month,
        int $year,
        string $monthLabel,
        string $expectedFilenamePart
    ): void {
        $user = (new User())->setFirstName($firstName)->setLastName($lastName);
        $workMonth = (new WorkMonth())
            ->setMonth($month)
            ->setYear($year)
            ->setUser($user);

        $monthNameHelper = $this->createMock(MonthNameHelper::class);
        $monthNameHelper->expects($this->once())
            ->method('getLocalizedMonthName')
            ->willReturn($monthLabel)
        ;

        $slugHelper = new SlugHelper();
        $this->tempDir = sys_get_temp_dir() . '/timeboard_pdf_test_' . uniqid();

        $fileNamer = new FileNamer($monthNameHelper, $slugHelper);
        $config = new TimeSheetConfig(
            templatePath: '/fake/template',
            pdfPath: $this->tempDir,
            imgPath: '/fake/img',
            logoFilename: 'logo.png',
            signatureFilename: 'sign.png'
        );
        $directoryManager = new DirectoryManager($config);
        $spreadsheet = $this->createMock(Spreadsheet::class);

        $builder = $this->createMock(TimeSheetBuilder::class);
        $builder->expects($this->once())
            ->method('build')
            ->willReturn($spreadsheet)
        ;

        $writer = $this->createMock(SpreadsheetWriterInterface::class);
        $writer->expects($this->once())
            ->method('write')
            ->with(
                $spreadsheet,
                $this->callback(fn(string $path) => str_contains($path, $expectedFilenamePart))
            );

        $logger = $this->createMock(LoggerInterface::class);
        $exporter = new TimeSheetExporter($builder, $writer, $fileNamer, $directoryManager);
        $generator = new TimeSheetPdfGenerator($exporter, $logger);

        $pdfPath = $generator->generate($workMonth);

        $this->assertDirectoryExists($this->tempDir);
        $this->assertStringContainsString($expectedFilenamePart, $pdfPath);
        $this->assertStringStartsWith($this->tempDir, $pdfPath);
    }

    public static function providePdfGenerationData(): array
    {
        return [
            'Jean Dupont juillet 2025' => [
                'Jean',
                'Dupont',
                7,
                2025,
                'juillet',
                'fiche_heure_dupont_jean_juillet_2025.pdf'
            ],
            'Marie Curie mai 2024' => [
                'Marie',
                'Curie',
                5,
                2024,
                'mai',
                'fiche_heure_curie_marie_mai_2024.pdf'
            ],
            'Émile Zola décembre 2023' => [
                'Émile',
                'Zola',
                12,
                2023,
                'décembre',
                'fiche_heure_zola_emile_decembre_2023.pdf'
            ],
        ];
    }

    protected function tearDown(): void
    {
        if (isset($this->tempDir) && is_dir($this->tempDir)) {
            array_map('unlink', glob($this->tempDir . '/*') ?: []);
            rmdir($this->tempDir);
        }
    }

    /**
     * @throws Exception
     * @throws \ReflectionException
     */
    #[DataProvider('provideExportFailureScenarios')]
    public function testGenerateThrowsPdfGenerationExceptionAndLogsErrorFromVariousErrors(
        \Throwable $originalException,
        string $expectedLogMessageFragment
    ): void {
        $user = (new User())->setFirstName('Test')->setLastName('Erreur');
        $workMonth = (new WorkMonth())
            ->setMonth(1)
            ->setYear(2025)
            ->setUser($user);

        $reflection = new \ReflectionClass($workMonth);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($workMonth, 999);

        $monthNameHelper = $this->createMock(MonthNameHelper::class);
        $monthNameHelper->expects($this->once())
            ->method('getLocalizedMonthName')
            ->willReturn('janvier')
        ;

        $slugHelper = new SlugHelper();
        $fileNamer = new FileNamer($monthNameHelper, $slugHelper);

        $config = new TimeSheetConfig('/tpl', sys_get_temp_dir(), '/img', 'logo.png', 'sign.png');
        $directoryManager = new DirectoryManager($config);

        $builder = $this->createMock(TimeSheetBuilder::class);
        $builder->expects($this->once())
            ->method('build')
            ->willReturn($this->createMock(Spreadsheet::class))
        ;

        $writer = $this->createMock(SpreadsheetWriterInterface::class);
        $writer->expects($this->once())
            ->method('write')
            ->willThrowException($originalException)
        ;

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with(
                $this->stringContains($expectedLogMessageFragment),
                $this->callback(fn(array $context) => $context['workMonthId'] === 999)
            );

        $exporter = new TimeSheetExporter($builder, $writer, $fileNamer, $directoryManager);
        $generator = new TimeSheetPdfGenerator($exporter, $logger);

        $this->expectException(PdfGenerationException::class);
        $this->expectExceptionMessage('Échec de génération du PDF.');

        $generator->generate($workMonth);
    }

    public static function provideExportFailureScenarios(): array
    {
        return [
            'RuntimeException' => [
                new \RuntimeException('Erreur d\'écriture de fichier'),
                'Erreur d\'écriture de fichier'
            ],
            'LogicException' => [
                new \LogicException('Problème de logique'),
                'Problème de logique'
            ],
            'Erreur personnalisée' => [
                new class('Erreur métier') extends \Exception {},
                'Erreur métier'
            ],
            'Exception générique' => [
                new \Exception('Quelque chose a mal tourné'),
                'Quelque chose a mal tourné'
            ],
        ];
    }
}
