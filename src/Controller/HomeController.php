<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\WorkMonthRepository;
use App\Service\WorkMonthGrouper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        WorkMonthRepository $workMonthRepository,
        WorkMonthGrouper $workMonthGrouper
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $workMonths = $workMonthRepository->findAllForUser($user);
        $workMonthsByYear = $workMonthGrouper->groupByYearAndMonth($workMonths);

        return $this->render('home/index.html.twig', [
            'workMonthsByYear' => $workMonthsByYear,
        ]);
    }
}
