<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\PerformanceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\SeanceRepository;

class DashboardController extends AbstractController
{
   #[Route('/client/dashboard', name: 'client_dashboard')]
    public function client(
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

        $goal = (int) $client->getObjectifs();

        if ($goal <= 0) {
            $goal = 12;
        }

        $progressRate = round(($monthlyCount / $goal) * 100);

        // 🔥 Graph Objectifs
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

        return $this->render('dashboard/client.html.twig', [
            'monthlyCount' => $monthlyCount,
            'progressRate' => $progressRate,
            'goal' => $goal,
            'dates' => $dates,
            'charges' => $charges
        ]);
    }

    #[Route('/coach/dashboard', name: 'coach_dashboard')]
    public function coach(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COACH');

        return $this->render('dashboard/coach.html.twig');
    }
    

    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function admin(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('dashboard/admin.html.twig');
    }

    #[Route('/admin/users', name: 'admin_users')]
    public function users(UserRepository $userRepository, Request $request): Response   
    {
        $search = $request->query->get('search');

        if ($search) {
            $users = $userRepository->createQueryBuilder('u')
                ->where('u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search')
                ->setParameter('search', '%'.$search.'%')
                ->getQuery()
                ->getResult();
        } else {
            $users = $userRepository->findAll();
        }

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/seances', name: 'admin_seances')]
    public function seances(SeanceRepository $seanceRepository): Response
    {
        return $this->render('admin/seances.html.twig', [
            'seances' => $seanceRepository->findAll(),
        ]);
    }


    #[Route('/admin/user/delete/{id}', name: 'admin_user_delete', methods: ['POST'])]
    public function delete(User $user, EntityManagerInterface $em, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {

            $em->remove($user);
            $em->flush();
        }

        return $this->redirectToRoute('admin_users');
    }

    #[Route('/admin/user/edit/{id}', name: 'admin_user_edit')]
    public function edit(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/edit_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }




}
