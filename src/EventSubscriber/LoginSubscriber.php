<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

class LoginSubscriber implements EventSubscriberInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (method_exists($user, 'isMustChangePassword') && $user->isMustChangePassword()) {
            $event->setResponse(new RedirectResponse(
                $this->router->generate('change_password')
            ));
            return;
        }

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $event->setResponse(new RedirectResponse(
                $this->router->generate('admin_dashboard')
            ));
            return;
        }

        if (in_array('ROLE_COACH', $user->getRoles())) {
            $event->setResponse(new RedirectResponse(
                $this->router->generate('coach_dashboard')
            ));
            return;
        }

        if (in_array('ROLE_CLIENT', $user->getRoles())) {
            $event->setResponse(new RedirectResponse(
                $this->router->generate('client_dashboard')
            ));
        }
    }
}