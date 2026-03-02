<?php

namespace App\Controller;

use App\Entity\Seance;
use App\Form\SeanceType;
use App\Entity\Reservation;
use App\Repository\AvisRepository;
use App\Repository\ClientRepository;
use App\Repository\SeanceRepository;
use App\Repository\ObjectifRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\NotificationService;

#[Route('/coach')]
#[IsGranted('ROLE_COACH')]
class CoachController extends AbstractController
{

    public function __construct(
        private NotificationService $notificationService
    ) {}

    #[Route('/dashboard', name: 'coach_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('dashboard/coach.html.twig');
    }

    #[Route('/clients', name: 'coach_clients')]
    public function clients(
        ClientRepository $clientRepository,
        ObjectifRepository $objectifRepository,
        Request $request
    ): Response {

        $coach = $this->getUser()->getCoach();
        $filter = $request->query->get('filter');

        if ($filter === 'subscribed') {
            // Clients abonnés via ManyToMany
            $clients = $coach->getClients();
        } else {
            // Tous les clients
            $clients = $clientRepository->findAll();
        }

        $clientsWithObjectifs = [];

        foreach ($clients as $client) {

            $objectifs = $objectifRepository->findBy(
                ['user' => $client->getUser()],
                ['dateDebut' => 'DESC']
            );

            $clientsWithObjectifs[] = [
                'client' => $client,
                'objectifs' => $objectifs
            ];
        }

        return $this->render('coach/clients.html.twig', [
            'clientsData' => $clientsWithObjectifs,
            'filter' => $filter
        ]);
    }

    #[Route('/seances', name: 'coach_seances')]
    public function seances(
        Request $request,
        EntityManagerInterface $em,
        SeanceRepository $seanceRepository
    ): Response {

        $coach = $this->getUser()->getCoach();

        $seance = new Seance();
        $form = $this->createForm(SeanceType::class, $seance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $seance->setCoach($coach);
            $em->persist($seance);
            $em->flush();

            return $this->redirectToRoute('coach_seances');
        }

        $seances = $seanceRepository->findBy(
            ['coach' => $coach],
            ['date' => 'ASC']
        );

        return $this->render('coach/seances.html.twig', [
            'form' => $form->createView(),
            'seances' => $seances
        ]);
    }

    #[Route('/stats', name: 'coach_stats')]
    public function stats(AvisRepository $avisRepo): Response
    {
        $coach = $this->getUser()->getCoach();

        $average = $avisRepo->getAverageForCoach($coach);

        $avis = $avisRepo->createQueryBuilder('a')
            ->join('a.seance', 's')
            ->where('s.coach = :coach')
            ->setParameter('coach', $coach)
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('coach/stat.html.twig', [
            'average' => round($average, 1),
            'avis' => $avis
        ]);
    }

    #[Route('/profil', name: 'coach_profile')]
    public function profil(ClientRepository $clientRepository): Response
    {
        $coach = $this->getUser()->getCoach();
        $user = $this->getUser();

        $totalClients = $coach->getClients()->count();

        return $this->render('profile/profil.html.twig', [
            'coach' => $coach,
            'user' => $user,   // 🔥 AJOUTE ÇA
            'totalClients' => $totalClients
        ]);
    }
    

    #[Route('/reservation/valider/{id}', name: 'valider_reservation')]
    public function validerReservation(
        Reservation $reservation,
        EntityManagerInterface $em
    ): Response {

        $coach = $this->getUser()->getCoach();

        // 🔒 Sécurité
        if ($reservation->getSeance()->getCoach() !== $coach) {
            throw $this->createAccessDeniedException();
        }

        // 🔒 Empêche double validation
        if ($reservation->getStatut() !== 'EN_ATTENTE') {
            $this->addFlash('warning', 'Réservation déjà traitée.');
            return $this->redirectToRoute('coach_seances');
        }

        $reservation->setStatut('VALIDEE');
        $em->flush();

        // 🔔 Notification
        $this->notificationService->create(
            $reservation->getClient()->getUser(),
            sprintf(
                'Votre réservation pour la séance "%s" du %s a été validée.',
                $reservation->getSeance()->getType(),
                $reservation->getSeance()->getDate()->format('d/m à H:i')
            ),
            'RESERVATION_VALIDATED'
        );

        $this->addFlash('success', 'Réservation validée.');

        return $this->redirectToRoute('coach_seances');
    }

   #[Route('/reservation/refuser/{id}', name: 'refuser_reservation')]
    public function refuserReservation(
        Reservation $reservation,
        EntityManagerInterface $em
    ): Response {

        $coach = $this->getUser()->getCoach();

        // 🔒 Sécurité
        if ($reservation->getSeance()->getCoach() !== $coach) {
            throw $this->createAccessDeniedException();
        }

        // 🔒 Empêche double traitement
        if ($reservation->getStatut() !== 'EN_ATTENTE') {
            $this->addFlash('warning', 'Réservation déjà traitée.');
            return $this->redirectToRoute('coach_seances');
        }

        $reservation->setStatut('REFUSEE');
        $em->flush();

        // 🔔 Notification
        $this->notificationService->create(
            $reservation->getClient()->getUser(),
            sprintf(
                'Votre réservation pour la séance "%s" du %s a été refusée.',
                $reservation->getSeance()->getType(),
                $reservation->getSeance()->getDate()->format('d/m à H:i')
            ),
            'RESERVATION_REFUSED'
        );

        $this->addFlash('success', 'Réservation refusée.');

        return $this->redirectToRoute('coach_seances');
    }

}