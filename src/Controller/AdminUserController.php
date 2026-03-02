<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mailer\MailerInterface;
use App\Entity\AdminLog;

class AdminUserController extends AbstractController
{
  #[Route('/admin/new-user', name: 'admin_new_user')]
    public function newUser(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        MailerInterface $mailer
    ): Response
    {
        $currentUser = $this->getUser();

        if (!$currentUser || !$currentUser->isCtrlAdmin()) {
            throw $this->createAccessDeniedException();
        }

        $error = null;
        $tempPassword = null;

        if ($request->isMethod('POST')) {

            $nom = $request->request->get('nom');
            $prenom = $request->request->get('prenom');
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $confirm = $request->request->get('confirm_password');

            if (!$nom || !$prenom || !$email) {
                $error = "Tous les champs sont obligatoires.";
            }

            if (!$error) {

                $existing = $em->getRepository(User::class)->findOneBy(['email' => $email]);
                if ($existing) {
                    $error = "Cet email existe déjà.";
                }
            }

            if (!$error) {

                $user = new User();
                $user->setNom($nom);
                $user->setPrenom($prenom);
                $user->setEmail($email);
                $user->setRoles(['ROLE_ADMIN']);

                if ($password === $confirm && $password !== null) {
                    $hashed = $hasher->hashPassword($user, $password);
                    $user->setPassword($hashed);
                } else {
                    $tempPassword = bin2hex(random_bytes(4));
                    $hashed = $hasher->hashPassword($user, $tempPassword);
                    $user->setPassword($hashed);
                    $user->setMustChangePassword(true);
                }

                if ($request->request->get('ctrl_admin')) {
                    $user->setCtrlAdmin(true);
                }

                $em->persist($user);

                // 📜 LOG
                $log = new AdminLog();
                $log->setCreatedBy($currentUser);
                $log->setCreatedUserEmail($email);
                $log->setCreatedAt(new \DateTime());
                $em->persist($log);

                $em->flush();

                // 📧 ENVOI EMAIL si mot de passe temporaire
                if ($tempPassword) {
                    $emailMessage = (new Email())
                        ->from('no-reply@sportpro.com')
                        ->to($email)
                        ->subject('Votre compte administrateur SportPro')
                        ->html("
                            <h2>Bienvenue $prenom</h2>
                            <p>Un compte administrateur a été créé pour vous.</p>
                            <p><strong>Mot de passe temporaire :</strong> $tempPassword</p>
                            <p>Vous devrez le modifier à votre première connexion.</p>
                        ");

                    $mailer->send($emailMessage);
                }

                return $this->redirectToRoute('admin_users');
            }
        }

        return $this->render('admin/nv_user.html.twig', [
            'error' => $error
        ]);
    }
}