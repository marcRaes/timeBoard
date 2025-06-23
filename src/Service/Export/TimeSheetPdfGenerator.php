<?php

namespace App\Service\Export;

use App\Entity\WorkMonth;
use App\Exception\PdfGenerationException;
use Psr\Log\LoggerInterface;

readonly class TimeSheetPdfGenerator implements TimeSheetPdfGeneratorInterface
{
    public function __construct(
        private TimeSheetExporter $exporter,
        private LoggerInterface $logger
    ) {}

    public function generate(WorkMonth $workMonth): string
    {
        try {
            return $this->exporter->export($workMonth);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur PDF : ' . $e->getMessage(), [
                'workMonthId' => $workMonth->getId(),
            ]);
            throw new PdfGenerationException('Échec de génération du PDF.', 0, $e);
        }
    }
}
