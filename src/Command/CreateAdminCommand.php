<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Créer un utilisateur administrateur (ctrl_admin requis)'
)]
class CreateAdminCommand extends Command
{
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        // 🔐 Vérification créateur
        $creatorEmail = $helper->ask($input, $output, new Question('Email de l’admin créateur : '));

        $creator = $this->em->getRepository(User::class)->findOneBy([
            'email' => $creatorEmail
        ]);

        if (!$creator || !$creator->isCtrlAdmin()) {
            $output->writeln('<error>Accès refusé. Vous n\'êtes pas autorisé à créer un administrateur.</error>');
            return Command::FAILURE;
        }

        // 📌 Infos nouvel admin
        $nom = $helper->ask($input, $output, new Question('Nom : '));
        $prenom = $helper->ask($input, $output, new Question('Prénom : '));
        $email = $helper->ask($input, $output, new Question('Email : '));

        $passwordQuestion = new Question('Mot de passe : ');
        $passwordQuestion->setHidden(true);
        $passwordQuestion->setHiddenFallback(false);
        $password = $helper->ask($input, $output, $passwordQuestion);

        $newAdmin = new User();
        $newAdmin->setNom($nom);
        $newAdmin->setPrenom($prenom);
        $newAdmin->setEmail($email);
        $newAdmin->setRoles(['ROLE_ADMIN']);
        $newAdmin->setCtrlAdmin(false); 

        $hashedPassword = $this->passwordHasher->hashPassword($newAdmin, $password);
        $newAdmin->setPassword($hashedPassword);

        $this->em->persist($newAdmin);
        $this->em->flush();

        $output->writeln('<info>Nouvel administrateur créé avec succès.</info>');

        return Command::SUCCESS;
    }
}