<?php

namespace App\Controller;

use App\Entity\WorkMonth;
use App\Service\TimeSheetExporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

final class TimeSheetController extends AbstractController
{
    #[Route('/timesheet/pdf/{id}', name: 'app_timesheet_pdf')]
    public function timesheet_pdf(WorkMonth $workMonth, TimeSheetExporter $timeSheetExporter): BinaryFileResponse
    {
        $filePath = $timeSheetExporter->create($workMonth);

        return new BinaryFileResponse( $filePath, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ResponseHeaderBag::DISPOSITION_ATTACHMENT . '; filename="' . basename($filePath) . '"',
        ]);
    }
}
