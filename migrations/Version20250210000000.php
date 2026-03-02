<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250210000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout ctrl_admin et must_change_password dans user';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user ADD ctrl_admin TINYINT(1) NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE user ADD must_change_password TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP ctrl_admin');
        $this->addSql('ALTER TABLE user DROP must_change_password');
    }
}