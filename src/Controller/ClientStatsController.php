<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ClientStatsController extends AbstractController
{
    #[Route('/client/stats/api', name: 'client_stats_api')]
    public function stats(): JsonResponse
    {
        // Fake data (à remplacer par vraie logique BDD)
        return new JsonResponse([
            'total' => 24,
            'month' => 5,
            'progress' => 18,
            'labels' => ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun'],
            'values' => [2, 4, 3, 5, 6, 4],
        ]);
    }
}