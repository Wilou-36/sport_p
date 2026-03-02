<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SiteController extends AbstractController
{
    #[Route('/pole-cned', name: 'pole_cned')]
    public function poleCned(): Response
    {
        return $this->render('pages/pole_cned.html.twig');
    }

    #[Route('/groupe-elite', name: 'groupe_elite')]
    public function groupeElite(): Response
    {
        return $this->render('pages/groupe_elite.html.twig');
    }

    #[Route('/nous-contacter', name: 'nous_contacter')]
    public function contact(): Response
    {
        return $this->render('pages/contact.html.twig');
    }
}
