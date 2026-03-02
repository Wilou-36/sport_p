<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Client;
use App\Entity\Coach;
use App\Entity\Sport;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\SecurityBundle\Security;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        Security $security
    ): Response {

        $user = new User();

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_USER']);

            $entityManager->persist($user);
            $entityManager->flush();

            $security->login($user);

            return $this->redirectToRoute('choose_role');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/choose-role', name: 'choose_role')]
    #[IsGranted('ROLE_USER')]
    public function chooseRole(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if ($request->isMethod('POST')) {

            $role = $request->request->get('role');

            if ($role === 'client') {
                $user->setRoles(['ROLE_CLIENT']);
            }

            if ($role === 'coach') {
                $user->setRoles(['ROLE_COACH']);
            }

            $em->flush();

            return $this->redirectToRoute('complete_profile');
        }

        return $this->render('registration/choose_role.html.twig');
    }

    #[Route('/complete-profile', name: 'complete_profile')]
    #[IsGranted('ROLE_USER')]
    public function completeProfile(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $sports = $em->getRepository(Sport::class)->findAll();

        if ($request->isMethod('POST')) {

            // ===== CLIENT =====
            if (in_array('ROLE_CLIENT', $user->getRoles())) {

                $client = new Client();
                $client->setUser($user);

                // IMPORTANT : récupérer nom/prenom depuis User
                $client->setNom($user->getNom());
                $client->setPrenom($user->getPrenom());

                $client->setAge($request->request->get('age'));
                $client->setTelephone($request->request->get('telephone'));
                $client->setTaille($request->request->get('taille'));
                $client->setPoids($request->request->get('poids'));

                // SPORT
                $sportId = $request->request->get('sport_id');
                if ($sportId) {
                    $sport = $em->getRepository(Sport::class)->find($sportId);
                    $client->setSport($sport);
                }

                $em->persist($client);
                $em->flush();

                return $this->redirectToRoute('client_dashboard');
            }

            // ===== COACH =====
            if (in_array('ROLE_COACH', $user->getRoles())) {

                $coach = new Coach();
                $coach->setUser($user);
                $coach->setSpecialite($request->request->get('specialite'));
                $coach->setExperience($request->request->get('experience'));

                $em->persist($coach);
                $em->flush();

                return $this->redirectToRoute('coach_dashboard');
            }
        }

        return $this->render('registration/complete_profile.html.twig', [
            'sports' => $sports
        ]);
    }
}