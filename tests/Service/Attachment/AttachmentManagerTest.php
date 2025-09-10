<?php

namespace App\Tests\Service\Attachment;

use App\Entity\WorkMonth;
use App\Exception\InvalidAttachmentException;
use App\Service\Attachment\AttachmentManager;
use App\Service\Attachment\AttachmentNameGenerator;
use App\Service\Attachment\AttachmentValidator;
use App\Service\Helper\SlugHelper;
use App\Service\MonthNameHelper;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

class AttachmentManagerTest extends TestCase
{
    private string $validFilePath;

    protected function setUp(): void
    {
        $this->validFilePath = tempnam(sys_get_temp_dir(), 'attch_') . '.pdf';
        file_put_contents($this->validFilePath, '%PDF-1.4');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->validFilePath)) {
            unlink($this->validFilePath);
        }
    }

    /**
     * @throws Exception
     */
    public function testGetAttachmentFileNameReturnsValidName(): void
    {
        $month = $this->createConfiguredMock(WorkMonth::class, [
            'getMonth' => 3,
            'getYear' => 2025,
        ]);

        $monthNameHelper = $this->createMock(MonthNameHelper::class);
        $monthNameHelper->method('getLocalizedMonthName')->with(3)->willReturn('mars');

//        $slugHelper = $this->createMock(SlugHelper::class);
//        $slugHelper->method('slugify')->with('mars')->willReturn('mars');

        $slugHelper = new SlugHelper();

        $attachmentValidator = $this->createMock(AttachmentValidator::class);
        $attachmentValidator->method('validate')->with($this->validFilePath)->willReturn('pdf');

        $generator = new AttachmentNameGenerator($monthNameHelper, $slugHelper, $attachmentValidator);
        $manager = new AttachmentManager($generator, $slugHelper, $monthNameHelper);

        $filename = $manager->getAttachmentFileName($month, $this->validFilePath);

        $this->assertSame('justificatif_transport_mars_2025.pdf', $filename);
    }

    /**
     * @throws Exception
     */
    public function testGetLocalizedMonthSlug(): void
    {
        $month = $this->createConfiguredMock(WorkMonth::class, ['getMonth' => 7]);

        $monthNameHelper = $this->createMock(MonthNameHelper::class);
        $monthNameHelper->method('getLocalizedMonthName')->with(7)->willReturn('juillet');

        $slugHelper = new SlugHelper();

        $dummyValidator = new AttachmentValidator();
        $generator = new AttachmentNameGenerator($monthNameHelper, $slugHelper, $dummyValidator);

        $manager = new AttachmentManager($generator, $slugHelper, $monthNameHelper);

        $slug = $manager->getLocalizedMonthSlug($month);

        $this->assertSame('juillet', $slug);
    }

    /**
     * @throws Exception
     */
    public function testGenerateThrowsIfFileDoesNotExist(): void
    {
        $this->expectException(InvalidAttachmentException::class);
        $this->expectExceptionMessage('Fichier justificatif introuvable.');

        $month = $this->createStub(WorkMonth::class);

        $generator = new AttachmentNameGenerator(
            $this->createMock(MonthNameHelper::class),
            new SlugHelper(),
            new AttachmentValidator(),
        );

        $generator->generate($month, '/path/to/missing/file.pdf');
    }

    public function testValidatorThrowsIfInvalidExtension(): void
    {
        $invalidFilePath = tempnam(sys_get_temp_dir(), 'attch_') . '.exe';
        file_put_contents($invalidFilePath, 'fake binary');

        $this->expectException(InvalidAttachmentException::class);
        $this->expectExceptionMessage('Extension non autorisée.');

        try {
            $validator = new AttachmentValidator();
            $validator->validate($invalidFilePath);
        } finally {
            unlink($invalidFilePath);
        }
    }

    public function testValidatorThrowsIfInvalidMime(): void
    {
        $invalidMimePath = tempnam(sys_get_temp_dir(), 'attch_') . '.pdf';
        file_put_contents($invalidMimePath, 'not a pdf');

        $fileMock = $this->getMockBuilder(File::class)
            ->setConstructorArgs([$invalidMimePath])
            ->onlyMethods(['getMimeType'])
            ->getMock();
        $fileMock->method('getMimeType')->willReturn('application/octet-stream');

        $this->expectException(InvalidAttachmentException::class);
        $this->expectExceptionMessage('Type MIME invalide.');

        try {
            $validator = new AttachmentValidator();
            $validator->validate($invalidMimePath);
        } finally {
            unlink($invalidMimePath);
        }
    }

    public function testSlugifyRemovesAccentsAndReplacesSpaces(): void
    {
        $slugHelper = new SlugHelper();

        $this->assertSame('ete_2025', $slugHelper->slugify('Été 2025'));
        $this->assertSame('mois_d_aout', $slugHelper->slugify('mois d’août'));
        $this->assertSame('mois_mars', $slugHelper->slugify('mois---mars'));
    }

    public function testValidateReturnsExtensionForValidPdf(): void
    {
        $validPdfPath = tempnam(sys_get_temp_dir(), 'attch_') . '.pdf';
        file_put_contents($validPdfPath, '%PDF-1.4');

        try {
            $validator = new AttachmentValidator();
            $extension = $validator->validate($validPdfPath);

            $this->assertSame('pdf', $extension);
        } finally {
            unlink($validPdfPath);
        }
    }
}
