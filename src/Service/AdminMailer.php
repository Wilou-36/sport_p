<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class AdminMailer
{
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function sendAdminCreatedEmail(string $to, string $temporaryPassword): void
    {
        $email = (new Email())
            ->from('no-reply@sportpro.com')
            ->to($to)
            ->subject('Votre compte administrateur SportPro')
            ->html($this->twig->render('emails/admin_created.html.twig', [
                'temporaryPassword' => $temporaryPassword
            ]));

        $this->mailer->send($email);
    }
}