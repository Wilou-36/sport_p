<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route('/notifications')]
class NotificationsController extends AbstractController
{
    #[Route('/', name: 'notifications_index')]
    public function index(): Response
    {
        $user = $this->getUser();

        $notifications = $user ? $user->getNotifications() : [];

        return $this->render('notifications/index.html.twig', [
            'notifications' => $notifications
        ]);
    }
}
