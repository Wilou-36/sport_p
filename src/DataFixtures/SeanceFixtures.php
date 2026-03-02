<?php

namespace App\DataFixtures;

use App\Entity\Seance;
use App\Repository\CoachRepository;
use App\Repository\ClientRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SeanceFixtures extends Fixture
{
    private CoachRepository $coachRepository;
    private ClientRepository $clientRepository;

    public function __construct(CoachRepository $coachRepository, ClientRepository $clientRepository)
    {
        $this->coachRepository = $coachRepository;
        $this->clientRepository = $clientRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $coachs = $this->coachRepository->findAll();
        $clients = $this->clientRepository->findAll();

        if (empty($coachs) || empty($clients)) {
            return;
        }

        $types = ['Musculation', 'Cardio', 'Yoga', 'Crossfit', 'Spécifique'];
        $lieux = ['Salle A', 'Salle B', 'Extérieur', 'En ligne', 'Autre'];

        for ($i = 0; $i < 10; $i++) {

            $seance = new Seance();

            $seance->setType($types[array_rand($types)]);
            $seance->setDate((new \DateTime())->modify("+$i days"));
            $seance->setDuree(rand(45, 90));
            $seance->setLieu($lieux[array_rand($lieux)]);

            $seance->setCoach($coachs[array_rand($coachs)]);
            $seance->setClient($clients[array_rand($clients)]);

            $manager->persist($seance);
        }

        $manager->flush();
    }
}