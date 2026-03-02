<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260302004933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix notification created_at column type from VARCHAR(255) to DATETIME';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notification CHANGE created_at created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE notification CHANGE created_at created_at VARCHAR(255) NOT NULL');
    }
}
