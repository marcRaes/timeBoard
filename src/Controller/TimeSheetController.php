<?php

namespace App\Controller;

use App\Entity\WorkMonth;
use App\Service\Export\TimeSheetExporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class TimeSheetController extends AbstractController
{
    #[Route('/timesheet/pdf/{id}', name: 'app_timesheet_pdf')]
    public function timesheet_pdf(WorkMonth $workMonth, TimeSheetExporter $timeSheetExporter): BinaryFileResponse
    {
        $filePath = $timeSheetExporter->export($workMonth);

        return new BinaryFileResponse( $filePath, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ResponseHeaderBag::DISPOSITION_ATTACHMENT . '; filename="' . basename($filePath) . '"',
        ]);
    }
}
