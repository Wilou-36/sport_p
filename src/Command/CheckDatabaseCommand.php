<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:check-db',
    description: 'Vérifie que les tables et colonnes importantes existent'
)]
class CheckDatabaseCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $conn = $this->em->getConnection();
        $schemaManager = $conn->createSchemaManager();

        $tables = $schemaManager->listTableNames();

        $checks = [
            'user' => ['ctrl_admin', 'must_change_password', 'email', 'roles'],
            'client' => ['age', 'objectifs', 'user_id'],
            'coach' => ['specialite', 'experience', 'user_id'],
        ];

        foreach ($checks as $table => $requiredColumns) {

            $output->writeln("\n<comment>Vérification table: $table</comment>");

            if (!in_array($table, $tables)) {
                $output->writeln("<error>❌ Table $table inexistante</error>");
                continue;
            }

            $columns = $schemaManager->listTableColumns($table);

            foreach ($requiredColumns as $col) {
                if (!array_key_exists($col, $columns)) {
                    $output->writeln("<error>❌ Colonne manquante : $col</error>");
                } else {
                    $output->writeln("<info>✅ OK : $col existe</info>");
                }
            }
        }

        $output->writeln("\n<info>Vérification terminée.</info>");

        return Command::SUCCESS;
    }
}

