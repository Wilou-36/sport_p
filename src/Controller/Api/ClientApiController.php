<?php

namespace App\Controller\Api;

use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/client')]
#[IsGranted('ROLE_CLIENT')]
class ClientApiController extends AbstractController
{
    private ReservationRepository $reservationRepo;
    private EntityManagerInterface $em;

    public function __construct(
        ReservationRepository $reservationRepo,
        EntityManagerInterface $em
    ) {
        $this->reservationRepo = $reservationRepo;
        $this->em = $em;
    }

    /*
     * =========================
     * DASHBOARD GLOBAL DATA
     * =========================
     */
    #[Route('/dashboard', name: 'api_client_dashboard', methods: ['GET'])]
    public function dashboard(): JsonResponse
    {
        $client = $this->getUser()->getClient();

        $now = new \DateTime();
        $firstDayMonth = new \DateTime('first day of this month');
        $firstDayPreviousMonth = new \DateTime('first day of last month');
        $lastDayPreviousMonth = new \DateTime('last day of last month');

        $totalSessions = $this->reservationRepo->countByClient($client);

        $currentMonthCount = $this->reservationRepo
            ->countByClientAndDateRange($client, $firstDayMonth, $now);

        $previousMonthCount = $this->reservationRepo
            ->countByClientAndDateRange($client, $firstDayPreviousMonth, $lastDayPreviousMonth);

        $progress = $previousMonthCount > 0
            ? round((($currentMonthCount - $previousMonthCount) / $previousMonthCount) * 100)
            : ($currentMonthCount > 0 ? 100 : 0);

        $monthlyGoal = $client->getMonthlyGoal() ?? 12;

        return $this->json([
            'total' => $totalSessions,
            'month' => $currentMonthCount,
            'previousMonth' => $previousMonthCount,
            'progress' => $progress,
            'goal' => $monthlyGoal,
            'goalReached' => $currentMonthCount >= $monthlyGoal
        ]);
    }

    /*
     * =========================
     * GRAPH DATA (6 derniers mois)
     * =========================
     */
    #[Route('/stats', name: 'api_client_stats', methods: ['GET'])]
    public function stats(): JsonResponse
    {
        $client = $this->getUser()->getClient();

        $labels = [];
        $values = [];
        $goalLine = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = new \DateTime("-$i month");
            $start = (clone $date)->modify('first day of this month');
            $end = (clone $date)->modify('last day of this month');

            $labels[] = $date->format('M');
            $count = $this->reservationRepo
                ->countByClientAndDateRange($client, $start, $end);

            $values[] = $count;
            $goalLine[] = $client->getMonthlyGoal() ?? 12;
        }

        return $this->json([
            'labels' => $labels,
            'values' => $values,
            'goal' => $goalLine
        ]);
    }

    /*
     * =========================
     * PROCHAINE SEANCE
     * =========================
     */
    #[Route('/next-session', name: 'api_client_next_session', methods: ['GET'])]
    public function nextSession(): JsonResponse
    {
        $client = $this->getUser()->getClient();

        $session = $this->reservationRepo->findNextSession($client);

        if (!$session) {
            return $this->json(null);
        }

        return $this->json([
            'location' => $session->getLocation(),
            'date' => $session->getDate()->format('d/m/Y'),
            'time' => $session->getDate()->format('H:i'),
            'type' => $session->getType()
        ]);
    }

    /*
     * =========================
     * LISTE DES SEANCES
     * =========================
     */
    #[Route('/sessions', name: 'api_client_sessions', methods: ['GET'])]
    public function sessions(): JsonResponse
    {
        $client = $this->getUser()->getClient();

        $sessions = $this->reservationRepo->findBy(
            ['client' => $client],
            ['date' => 'DESC']
        );

        $data = [];

        foreach ($sessions as $session) {
            $data[] = [
                'id' => $session->getId(),
                'date' => $session->getDate()->format('d/m/Y H:i'),
                'location' => $session->getLocation(),
                'type' => $session->getType(),
                'coach' => $session->getCoach()?->getUser()?->getPrenom()
            ];
        }

        return $this->json($data);
    }

    /*
     * =========================
     * MODIFIER OBJECTIF MENSUEL
     * =========================
     */
    #[Route('/goal', name: 'api_client_update_goal', methods: ['POST'])]
    public function updateGoal(Request $request): JsonResponse
    {
        $client = $this->getUser()->getClient();

        $data = json_decode($request->getContent(), true);

        if (!isset($data['goal']) || !is_numeric($data['goal'])) {
            return $this->json([
                'error' => 'Objectif invalide'
            ], 400);
        }

        $client->setMonthlyGoal((int)$data['goal']);

        $this->em->flush();

        return $this->json([
            'success' => true,
            'goal' => $client->getMonthlyGoal()
        ]);
    }
}