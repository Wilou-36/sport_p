<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {

            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('admin_dashboard');
            }

            if ($this->isGranted('ROLE_COACH')) {
                return $this->redirectToRoute('coach_dashboard');
            }

            if ($this->isGranted('ROLE_CLIENT')) {
                return $this->redirectToRoute('client_dashboard');
            }
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }


    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Symfony intercepte automatiquement cette route
        throw new \LogicException('Cette méthode ne doit jamais être appelée.');
    }
}
