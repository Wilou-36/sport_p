<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Service\AdminMailer;
use Symfony\Component\String\ByteString;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\SeanceRepository;
use App\Entity\Setting;
use App\Repository\SettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\UserRepository;


#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/users', name: 'admin_users')]
    public function gererUtilisateurs(): Response
    {
        return $this->render('admin/users.html.twig');
    }

    #[Route('/seances', name: 'admin_seances')]
    public function gererSeances(
        Request $request,
        SeanceRepository $seanceRepository
    ): Response {

        $coach = $request->query->get('coach');
        $date = $request->query->get('date');
        $lieu = $request->query->get('lieu');
        $reservation = $request->query->get('reservation');

        $qb = $seanceRepository->createQueryBuilder('s')
            ->leftJoin('s.coach', 'c')
            ->leftJoin('c.user', 'u')
            ->leftJoin('s.reservations', 'r')
            ->addSelect('c', 'u', 'r');

        if ($coach) {
            $qb->andWhere('u.nom LIKE :coach OR u.prenom LIKE :coach')
            ->setParameter('coach', "%$coach%");
        }

        if ($date) {
            $qb->andWhere('DATE(s.date) = :date')
            ->setParameter('date', $date);
        }

        if ($lieu) {
            $qb->andWhere('s.lieu LIKE :lieu')
            ->setParameter('lieu', "%$lieu%");
        }

        if ($reservation === 'oui') {
            $qb->andWhere('r.id IS NOT NULL');
        }

        if ($reservation === 'non') {
            $qb->andWhere('r.id IS NULL');
        }

        $seances = $qb->getQuery()->getResult();

        return $this->render('admin/seances.html.twig', [
            'seances' => $seances
        ]);
    }

    #[Route('/settings', name: 'admin_settings')]
    public function settings(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {

        $admin = $this->getUser();

        if (!$admin) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {

            $admin->setNom($request->request->get('nom'));
            $admin->setPrenom($request->request->get('prenom'));

            if ($admin->isCtrlAdmin()) {
                $admin->setEmail($request->request->get('email'));
                $admin->setIsVerified($request->request->get('is_verified') === 'on');
                $admin->setMustChangePassword($request->request->get('must_change_password') === 'on');
            }

            if ($request->request->get('new_password')) {
                $hashedPassword = $hasher->hashPassword(
                    $admin,
                    $request->request->get('new_password')
                );
                $admin->setPassword($hashedPassword);
            }

            $em->flush();
        }

        return $this->render('admin/settings.html.twig', [
            'admin' => $admin
        ]);
    }


    #[Route('/admin/users', name: 'admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,   
        ]);
    }
    

    #[Route('/admin/users/new', name: 'admin_new_user')]
    public function newAdmin(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        AdminMailer $mailer
    ): Response {

        if (!$this->getUser()->isCtrlAdmin()) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {

            $email = $request->request->get('email');
            $nom = $request->request->get('nom');
            $prenom = $request->request->get('prenom');

            $temporaryPassword = ByteString::fromRandom(10)->toString();

            $user = new User();
            $user->setEmail($email);
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setRoles(['ROLE_ADMIN']);
            $user->setMustChangePassword(true);

            $hashedPassword = $hasher->hashPassword($user, $temporaryPassword);
            $user->setPassword($hashedPassword);

            $em->persist($user);
            $em->flush();

            // 📧 ENVOI EMAIL
            $mailer->sendAdminCreatedEmail($email, $temporaryPassword);

            $this->addFlash('success', 'Admin créé et email envoyé.');

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/nv_user.html.twig');
    }

    #[Route('/force-change-password', name: 'app_force_change_password')]
    public function forceChangePassword(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {

        $user = $this->getUser();

        if (!$user->isMustChangePassword()) {
            return $this->redirectToRoute('admin_dashboard');
        }

        if ($request->isMethod('POST')) {

            $newPassword = $request->request->get('password');

            $hashedPassword = $hasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
            $user->setMustChangePassword(false);

            $em->flush();

            $this->addFlash('success', 'Mot de passe mis à jour.');

            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('security/force_change_password.html.twig');
    }
}
