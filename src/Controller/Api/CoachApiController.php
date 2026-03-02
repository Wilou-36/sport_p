<?php

namespace App\Controller\Api;

use App\Entity\Seance;
use App\Repository\SeanceRepository;
use App\Repository\ReservationRepository;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/coach')]
#[IsGranted('ROLE_COACH')]
class CoachApiController extends AbstractController
{
    #[Route('/seances', name: 'api_coach_seances', methods: ['GET'])]
    public function list(SeanceRepository $repo): JsonResponse
    {
        $coach = $this->getUser()->getCoach();

        $seances = $repo->createQueryBuilder('s')
            ->where('s.coach = :coach')
            ->setParameter('coach', $coach)
            ->orderBy('s.date', 'ASC') // ✅ corrigé
            ->getQuery()
            ->getResult();

        $data = [];

        foreach ($seances as $seance) {
            $data[] = [
                'id' => $seance->getId(),
                'type' => $seance->getType(),
                'lieu' => $seance->getLieu(),
                'duree' => $seance->getDuree(),
                'date' => $seance->getDate()?->format('Y-m-d H:i'),
                'capaciteMax' => $seance->getCapaciteMax(),
                'placesRestantes' => $seance->getCapaciteMax() - $seance->getReservations()->count()
            ];
        }

        return $this->json($data);
    }

    #[Route('/seances', name: 'api_coach_create_seance', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Données invalides'], 400);
        }

        $seance = new Seance();
        $seance->setType($data['type'] ?? null);
        $seance->setLieu($data['lieu'] ?? null);
        $seance->setDuree($data['duree'] ?? 0);
        $seance->setCapaciteMax($data['capaciteMax'] ?? 1);
        $seance->setDate(new \DateTime($data['date']));
        $seance->setCoach($this->getUser()->getCoach());

        $em->persist($seance);
        $em->flush();

        return $this->json([
            'message' => 'Séance créée',
            'id' => $seance->getId()
        ], 201);
    }

    #[Route('/seances/{id}', name: 'api_coach_delete_seance', methods: ['DELETE'])]
    public function delete(Seance $seance, EntityManagerInterface $em): JsonResponse
    {
        // 🔐 Sécurité : le coach ne peut supprimer que SES séances
        if ($seance->getCoach() !== $this->getUser()->getCoach()) {
            return $this->json(['error' => 'Accès refusé'], 403);
        }

        $em->remove($seance);
        $em->flush();

        return $this->json(['message' => 'Séance supprimée']);
    }

    #[Route('/stats', name: 'api_coach_stats', methods: ['GET'])]
    public function stats(AvisRepository $avisRepository): JsonResponse
    {
        $coach = $this->getUser()->getCoach();

        $stats = $avisRepository->getStatsForCoach($coach);

        // Derniers avis
        $latestAvis = $avisRepository->createQueryBuilder('a')
            ->join('a.seance', 's')
            ->where('s.coach = :coach')
            ->setParameter('coach', $coach)
            ->orderBy('a.id', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        $avisData = [];

        foreach ($latestAvis as $avis) {
            $avisData[] = [
                'note' => $avis->getNote(),
                'commentaire' => $avis->getCommentaire(),
                'date' => $avis->getSeance()->getDate()->format('Y-m-d')
            ];
        }

        return $this->json([
            'moyenne' => $stats['moyenne'],
            'totalAvis' => $stats['total'],
            'distribution' => $stats['distribution'],
            'derniersAvis' => $avisData
        ]);
    }

    #[Route('/reservations/count', name: 'api_coach_reservations_count', methods: ['GET'])]
    public function reservationsCount(ReservationRepository $reservationRepository): JsonResponse
    {
        $coach = $this->getUser()->getCoach();

        $count = $reservationRepository->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->join('r.seance', 's')
            ->where('s.coach = :coach')
            ->setParameter('coach', $coach) 
            ->getQuery()
            ->getSingleScalarResult();

        return $this->json(['count' => $count]);
    }
}