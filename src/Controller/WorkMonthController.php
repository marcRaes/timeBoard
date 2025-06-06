<?php

namespace App\Controller;

use App\Entity\WorkMonth;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/work-month')]
final class WorkMonthController extends AbstractController
{
    #[Route('/show/{id}', name: 'app_work_month_show', methods: ['GET'])]
    public function show(WorkMonth $workMonth): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $workMonth, 'Vous n\'êtes pas autorisé à voir ces journées de travail.');

        return $this->render('work_month/show.html.twig', [
            'workMonth' => $workMonth,
        ]);
    }
}
