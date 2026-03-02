<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-client',
    description: 'Créer un client'
)]
class CreateClientCommand extends Command
{
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        // USER
        $nom = $helper->ask($input, $output, new Question('Nom : '));
        $prenom = $helper->ask($input, $output, new Question('Prénom : '));
        $email = $helper->ask($input, $output, new Question('Email : '));
        $password = $helper->ask($input, $output, new Question('Mot de passe : '));

        $user = new User();
        $user->setNom($nom);
        $user->setPrenom($prenom);
        $user->setEmail($email);
        $user->setRoles(['ROLE_CLIENT']);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->em->persist($user);

        // CLIENT
        $age = $helper->ask($input, $output, new Question('Âge : '));
        $objectifs = $helper->ask($input, $output, new Question('Objectifs : '));

        $client = new Client();
        $client->setAge($age);
        $client->setObjectifs($objectifs);
        $client->setUser($user);

        $this->em->persist($client);

        $this->em->flush();

        $output->writeln('Client créé avec succès !');

        return Command::SUCCESS;
    }
}