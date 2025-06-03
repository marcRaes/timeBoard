<?php

namespace App\Service;

use App\Entity\WorkMonth;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Contracts\Translation\TranslatorInterface;

class TimeSheetExporter
{
    private Spreadsheet $spreadsheet;

    public function __construct(
        private readonly TranslatorInterface   $translator,
        private readonly WorkDurationFormatter $formatter,
        private readonly string $templatePath,
        private readonly string $pdfPath,
        private readonly string $imgPath,
        private readonly string $logoFilename,
        private readonly string $signatureFilename
    ) {
        $this->spreadsheet = IOFactory::load($this->templatePath);
    }

    public function create(WorkMonth $workMonth): string
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        $this->addHeader($sheet, $workMonth);
        $this->addLines($sheet, $workMonth);
        $this->ensureDirectoryExists();

        $pathPdf = $this->pdfPath . $this->generateFileName($workMonth);

        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A4);

        (new Mpdf($this->spreadsheet))->save($pathPdf);

        return $pathPdf;
    }

    private function generateFileName(WorkMonth $workMonth): string
    {
        return sprintf(
            'fiche_heure_%s_%s_%s_%s.pdf',
            strtolower($workMonth->getUser()->getLastName()),
            strtolower($workMonth->getUser()->getFirstName()),
            strtolower($this->getLocalizedMonthName($workMonth->getMonth())),
            $workMonth->getYear()
        );
    }

    private function getLocalizedMonthName(int $month): string
    {
        $date = \DateTime::createFromFormat('!m', $month);

        return $this->translator->trans($date->format('F'));
    }

    private function ensureDirectoryExists(): void
    {
        $dir = $this->pdfPath;
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }

    private function addHeader(Worksheet $sheet, WorkMonth $workMonth): void
    {
        $this->insertImage($sheet, $this->logoFilename, 'A2', 70);

        $sheet->setCellValue('E4', $this->getLocalizedMonthName($workMonth->getMonth()));
        $sheet->setCellValue('H4', $workMonth->getYear());
        $sheet->setCellValue('B8', $workMonth->getUser()->getLastName());
        $sheet->setCellValue('B10', $workMonth->getUser()->getFirstName());
    }

    private function addLines(Worksheet $sheet, WorkMonth $workMonth): void
    {
        $line = 15;
        $style = $this->getDefaultStyle();

        foreach ($workMonth->getWorkDays() as $workDay) {
            foreach ($workDay->getWorkPeriods() as $period) {
                $sheet->fromArray([
                    $workDay->getDate()->format('d/m/Y'),
                    $period->getTimeStart()->format('H:i'),
                    $period->getTimeEnd()->format('H:i'),
                    $this->formatter->format($period->getDuration()),
                    '',
                    $period->getLocation(),
                    '',
                    $period->getReplacedAgent()
                ], null, 'A' . $line);

                $sheet->mergeCells("D{$line}:E{$line}");
                $sheet->mergeCells("F{$line}:G{$line}");
                $sheet->mergeCells("H{$line}:N{$line}");

                $sheet->getStyle("A{$line}:N{$line}")->applyFromArray($style);
                $sheet->getRowDimension($line)->setRowHeight(15);

                $line++;
            }
        }

        $this->addFooter($sheet, $line, $workMonth);
    }

    private function addFooter(Worksheet $sheet, int $line, WorkMonth $workMonth): void
    {
        $boldCentered = $this->getDefaultStyle(true);

        $sheet->setCellValue("C{$line}", "TOTAL DES HEURES");
        $sheet->setCellValue("D{$line}", $workMonth->getFormattedTotalTime());
        $sheet->mergeCells("D{$line}:E{$line}");
        $sheet->getStyle("C{$line}:E{$line}")->applyFromArray($boldCentered);
        $sheet->getRowDimension($line)->setRowHeight(15);

        $line += 2;
        $label = $workMonth->getLunchTickets() . ' Ticket' . ($workMonth->getLunchTickets() > 1 ? 's' : '') . ' restaurant' . ($workMonth->getLunchTickets() > 1 ? 's' : '');
        $sheet->setCellValue("H{$line}", $label);
        $sheet->mergeCells("H{$line}:J{$line}");
        unset($boldCentered['borders']);
        $sheet->getStyle("H{$line}:J{$line}")->applyFromArray($boldCentered);
        $sheet->getRowDimension($line)->setRowHeight(15);

        $this->insertImage($sheet, $this->signatureFilename, "D" . ($line + 2), 70);
    }

    private function getDefaultStyle(bool $bold = false): array
    {
        return [
            'font' => ['bold' => $bold],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ];
    }

    private function insertImage(Worksheet $sheet, string $filename, string $coordinates, int $height): void
    {
        $drawing = new Drawing();
        $drawing->setWorksheet($sheet);
        $drawing->setName(pathinfo($filename, PATHINFO_FILENAME));
        $drawing->setDescription($filename);
        $drawing->setPath($this->imgPath . $filename);
        $drawing->setHeight($height);
        $drawing->setCoordinates($coordinates);
        $drawing->getShadow()->setVisible(true);
    }
}
