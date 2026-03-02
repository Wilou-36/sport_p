<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class SecurityController extends AbstractController
{

 #[Route('/change-password', name: 'change_password')]
    public function changePassword(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $error = null;

        if ($request->isMethod('POST')) {

            $password = $request->request->get('password');
            $confirm = $request->request->get('confirm_password');

            if (!$password || $password !== $confirm) {
                $error = "Les mots de passe ne correspondent pas.";
            }

            if (!$error) {
                $hashed = $hasher->hashPassword($user, $password);
                $user->setPassword($hashed);
                $user->setMustChangePassword(false);

                $em->flush();

                if ($this->isGranted('ROLE_ADMIN')) {
                    return $this->redirectToRoute('admin_dashboard');
                }

                if ($this->isGranted('ROLE_COACH')) {
                    return $this->redirectToRoute('coach_dashboard');
                }

                return $this->redirectToRoute('client_dashboard');
            }
        }

        return $this->render('security/change_password.html.twig', [
            'error' => $error
        ]);
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // récupère l'erreur s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        // dernier username entré
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }


}