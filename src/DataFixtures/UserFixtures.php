<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Client;
use App\Entity\Coach;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // ===== ADMIN =====
        $admin = new User();
        $admin->setNom('Do Rego');
        $admin->setPrenom('Williams');
        $admin->setEmail('williams.drg36@gmail.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'Admin123!Secure')
        );
        $admin->setIsVerified(true);

        $manager->persist($admin);


        // ===== COACH =====
        $coachUser = new User();
        $coachUser->setNom('Dupont');
        $coachUser->setPrenom('Jean');
        $coachUser->setEmail('coach@spp.fr');
        $coachUser->setRoles(['ROLE_COACH']);
        $coachUser->setPassword(
            $this->passwordHasher->hashPassword($coachUser, 'Coach123!Secure')
        );
        $coachUser->setIsVerified(true);

        $manager->persist($coachUser);

        $coach = new Coach();
        $coach->setUser($coachUser);
        $coach->setSpecialite('Fitness');
        $coach->setExperience('5 ans');

        $manager->persist($coach);


        // ===== CLIENT =====
        $clientUser = new User();
        $clientUser->setNom('Martin');
        $clientUser->setPrenom('Lucas');
        $clientUser->setEmail('client@spp.fr');
        $clientUser->setRoles(['ROLE_CLIENT']);
        $clientUser->setPassword(
            $this->passwordHasher->hashPassword($clientUser, 'Client123!Secure')
        );
        $clientUser->setIsVerified(true);

        $manager->persist($clientUser);

        $client = new Client();
        $client->setUser($clientUser);
        $client->setNom('Martin');
        $client->setPrenom('Lucas');
        $client->setAge(25);
        $client->setObjectifs('Perdre du poids');

        $manager->persist($client);


        // ===== 10 USERS RANDOM =====
        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setNom('User'.$i);
            $user->setPrenom('Test'.$i);
            $user->setEmail('user'.$i.'@spp.fr');
            $user->setRoles(['ROLE_CLIENT']);
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, 'User123!Secure')
            );
            $user->setIsVerified(true);

            $manager->persist($user);

            $randClient = new Client();
            $randClient->setUser($user);
            $randClient->setNom('User'.$i);
            $randClient->setPrenom('Test'.$i);
            $randClient->setAge(rand(20, 60));
            $randClient->setObjectifs('Objectif '.$i);

            $manager->persist($randClient);
        }

        $manager->flush();
    }
}

