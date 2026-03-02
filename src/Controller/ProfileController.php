<?php

namespace App\Controller;

use App\Service\FileUploader;
use App\Repository\ObjectifRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/profile', name: 'profile_')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(ObjectifRepository $objectifRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $objectifActif = null;

        if (in_array('ROLE_CLIENT', $user->getRoles())) {
            $objectifActif = $objectifRepository->findObjectifActif($user);
        }

        return $this->render('profile/profil.html.twig', [
            'objectifActif' => $objectifActif
        ]);
    }

    #[Route('/edit', name: 'edit')]
    public function edit(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if ($request->isMethod('POST')) {

            $user->setPrenom($request->request->get('prenom'));
            $user->setNom($request->request->get('nom'));
            $user->setEmail($request->request->get('email'));

            if ($this->isGranted('ROLE_COACH')) {
                $coach = $user->getCoach();
                $coach->setTelephone($request->request->get('telephone'));
            }

            if ($this->isGranted('ROLE_CLIENT')) {
                $client = $user->getClient();
                $client->setTelephone($request->request->get('telephone'));
                $client->setTaille($request->request->get('taille'));
                $client->setPoids($request->request->get('poids'));
            }

            $em->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès.');

            return $this->redirectToRoute('home'); // 🔥 ici
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/upload-photo', name: 'upload_photo', methods: ['POST'])]
    public function uploadPhoto(
        Request $request,
        FileUploader $fileUploader,
        EntityManagerInterface $em
    ): Response {

        $user = $this->getUser();
        $file = $request->files->get('photo');

        if ($file) {
            $fileName = $fileUploader->upload($file);
            $user->setPhoto($fileName);
            $em->flush();
        }

        return $this->redirectToRoute('profile_index');
    }
}