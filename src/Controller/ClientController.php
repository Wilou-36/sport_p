<?php

namespace App\Controller;

use App\Entity\Seance;
use App\Entity\Reservation;
use App\Entity\Objectif;
use App\Repository\CoachRepository;
use App\Entity\Coach;
use App\Service\NotificationService;
use App\Repository\SeanceRepository;
use App\Repository\ObjectifRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ObjectifType;
use App\Repository\PerformanceRepository;


#[Route('/client', name: 'client_')]
class ClientController extends AbstractController
{

public function __construct(
        private NotificationService $notificationService
    ) {}
    
   #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(
        SeanceRepository $seanceRepository,
        PerformanceRepository $performanceRepository
    ): Response {

        $this->denyAccessUnlessGranted('ROLE_CLIENT');

        $user = $this->getUser();
        $client = $user->getClient();

        $startOfMonth = new \DateTime('first day of this month');
        $endOfMonth = new \DateTime('last day of this month 23:59:59');

        $monthlySessions = $seanceRepository->createQueryBuilder('s')
            ->andWhere('s.client = :client')
            ->andWhere('s.date BETWEEN :start AND :end')
            ->setParameter('client', $client)
            ->setParameter('start', $startOfMonth)
            ->setParameter('end', $endOfMonth)
            ->getQuery()
            ->getResult();

        $monthlyCount = count($monthlySessions);

        $goal = $client->getMonthlyGoal() ?? 12;

        $progressRate = $goal > 0 
            ? round(($monthlyCount / $goal) * 100)
            : 0;

        // 🔥 Récupération performances pour le graph
        $performances = $performanceRepository->findBy(
            ['user' => $user],
            ['date' => 'ASC']
        );

        $dates = [];
        $charges = [];

        foreach ($performances as $perf) {
            $dates[] = $perf->getDate()->format('d/m');
            $charges[] = $perf->getCharge();
        }

        return $this->render('client/dashboard.html.twig', [
            'monthlyCount' => $monthlyCount,
            'progressRate' => $progressRate,
            'goal' => $goal,
            'dates' => $dates,     // ← IMPORTANT
            'charges' => $charges  // ← IMPORTANT
        ]);
    }

    #[Route('/seances', name: 'seances', methods: ['GET'])]
    public function seances(SeanceRepository $seanceRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CLIENT');

        $user = $this->getUser();

        // Vérification sécurité supplémentaire
        if (!$user || !$user->getClient()) {
            throw $this->createAccessDeniedException('Client non associé.');
        }

        $client = $user->getClient();

        $nextSeance = $seanceRepository->findNextSeanceForClient($client);
        return $this->render('client/seances.html.twig', [
            'nextSeance' => $nextSeance
        ]);
    }

   #[Route('/reservations', name: 'reservations')]
    public function reservations(
        Request $request,
        SeanceRepository $repo
    ): Response {

        $lieu = $request->query->get('lieu');
        $type = $request->query->get('type');

        $qb = $repo->createQueryBuilder('s')
            ->where('s.date > :now')
            ->setParameter('now', new \DateTime());

        if ($lieu) {
            $qb->andWhere('s.lieu LIKE :lieu')
            ->setParameter('lieu', "%$lieu%");
        }

        if ($type) {
            $qb->andWhere('s.type LIKE :type')
            ->setParameter('type', "%$type%");
        }

        return $this->render('client/reservations.html.twig', [
            'seances' => $qb->getQuery()->getResult()
        ]);
    }

    #[Route('/reserver/{id}', name: 'reserver')]
    public function reserver(
        int $id,
        SeanceRepository $seanceRepository,
        EntityManagerInterface $em
    ): Response {

        $this->denyAccessUnlessGranted('ROLE_CLIENT');

        $client = $this->getUser()->getClient();

        $seance = $seanceRepository->find($id);

        if (!$seance) {
            throw $this->createNotFoundException('Séance introuvable.');
        }

        if ($seance->getPlacesRestantes() <= 0) {
            $this->addFlash('error', 'Séance complète.');
            return $this->redirectToRoute('client_reservations');
        }

        $reservation = new Reservation();
        $reservation->setClient($client);
        $reservation->setSeance($seance);
        $reservation->setStatut('EN_ATTENTE');

        $em->persist($reservation);
        $em->flush();

        $this->addFlash('success', 'Réservation envoyée. En attente de validation.');

        return $this->redirectToRoute('client_reservations');
    }

    #[Route('/objectifs', name: 'objectifs')]
    public function objectifs(
        ObjectifRepository $objectifRepository,
        PerformanceRepository $performanceRepository
    ): Response {

        $this->denyAccessUnlessGranted('ROLE_CLIENT');

        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $objectifs = $objectifRepository->findBy(
            ['user' => $user],
            ['dateDebut' => 'DESC']
        );

        $objectifActif = $objectifRepository->findObjectifActif($user);

        $performances = $performanceRepository->findBy(
            ['user' => $user],
            ['date' => 'ASC']
        );

        $dates = [];
        $charges = [];

        foreach ($performances as $perf) {
            $dates[] = $perf->getDate()->format('d/m');
            $charges[] = $perf->getCharge();
        }

        return $this->render('client/objectifs.html.twig', [
            'objectifs' => $objectifs,
            'objectifActif' => $objectifActif,
            'dates' => $dates,
            'charges' => $charges
        ]);
    }

    #[Route('/objectif/add', name: 'objectif_add')]
    public function addObjectif(
        Request $request,
        EntityManagerInterface $em
    ): Response {

        $this->denyAccessUnlessGranted('ROLE_CLIENT');

        $objectif = new Objectif();
        $form = $this->createForm(ObjectifType::class, $objectif);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $objectif->setUser($this->getUser());
            $em->persist($objectif);
            $em->flush();

            return $this->redirectToRoute('client_objectifs');
            $this->notificationService->create(
                $coach->getUser(),
                "Un nouveau sportif s'est abonné à votre profil.",
                "NEW_SUBSCRIBER"
            );
        }

        return $this->render('client/objectif_form.html.twig', [
            'form' => $form->createView()
        ]);
    }


    #[Route('/settings', name: 'settings', methods: ['GET'])]
    public function settings(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CLIENT');

        return $this->render('client/settings.html.twig');
    }

     #[Route('/profil', name: 'profil', methods: ['GET'])]
    public function profil(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CLIENT');

        return $this->render('profile/profil.html.twig');
    }

    #[Route('/coachs', name: 'coachs')]
    public function coachs(CoachRepository $coachRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CLIENT');

        $coachs = $coachRepository->findAll();

        return $this->render('client/coachs.html.twig', [
            'coachs' => $coachs
        ]);
    }

   #[Route('/coach/subscribe/{id}', name: 'coach_subscribe')]
    public function subscribe(
        Coach $coach,
        EntityManagerInterface $em
    ): Response {

        $this->denyAccessUnlessGranted('ROLE_CLIENT');

        $client = $this->getUser()->getClient();

        $client->addCoach($coach);

        $em->flush();

        // 🔔 Notification pour le coach
        $this->notificationService->create(
            $coach->getUser(),
            $client->getUser()->getPrenom() . " s'est abonné à votre profil.",
            "NEW_SUBSCRIBER"
        );

        $this->addFlash('success', 'Abonnement au coach réussi.');

        return $this->redirectToRoute('client_coachs');
    }

}