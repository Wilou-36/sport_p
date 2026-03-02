<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260301214839 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admin_log (id INT AUTO_INCREMENT NOT NULL, created_user_email VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, created_by_id INT DEFAULT NULL, INDEX IDX_F9383BB0B03A8386 (created_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE client_coach (client_id INT NOT NULL, coach_id INT NOT NULL, INDEX IDX_9C34F17B19EB6921 (client_id), INDEX IDX_9C34F17B3C105691 (coach_id), PRIMARY KEY (client_id, coach_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE setting (id INT AUTO_INCREMENT NOT NULL, app_name VARCHAR(255) DEFAULT NULL, contact_email VARCHAR(255) DEFAULT NULL, default_language VARCHAR(5) DEFAULT NULL, session_timeout INT DEFAULT NULL, force_password_change TINYINT NOT NULL, two_factor TINYINT NOT NULL, notify_new_booking TINYINT NOT NULL, notify_cancel TINYINT NOT NULL, theme VARCHAR(20) DEFAULT NULL, primary_color VARCHAR(10) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE admin_log ADD CONSTRAINT FK_F9383BB0B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE client_coach ADD CONSTRAINT FK_9C34F17B19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE client_coach ADD CONSTRAINT FK_9C34F17B3C105691 FOREIGN KEY (coach_id) REFERENCES coach (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0E3797A94 FOREIGN KEY (seance_id) REFERENCES seance (id)');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF019EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE avis RENAME INDEX idx_avis_seance TO IDX_8F91ABF0E3797A94');
        $this->addSql('ALTER TABLE avis RENAME INDEX idx_avis_client TO IDX_8F91ABF019EB6921');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY `FK_CLIENT_SPORT`');
        $this->addSql('ALTER TABLE client DROP coach_id, CHANGE sport_id sport_id INT NOT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455AC78BCF8 FOREIGN KEY (sport_id) REFERENCES sport (id)');
        $this->addSql('ALTER TABLE client RENAME INDEX fk_client_sport TO IDX_C7440455AC78BCF8');
        $this->addSql('ALTER TABLE coach DROP FOREIGN KEY `FK_COACH_SPORT`');
        $this->addSql('ALTER TABLE coach CHANGE sport_id sport_id INT NOT NULL');
        $this->addSql('ALTER TABLE coach ADD CONSTRAINT FK_3F596DCCAC78BCF8 FOREIGN KEY (sport_id) REFERENCES sport (id)');
        $this->addSql('ALTER TABLE coach RENAME INDEX fk_coach_sport TO IDX_3F596DCCAC78BCF8');
        $this->addSql('ALTER TABLE objectif CHANGE vo2_objectif vo2_objectif DOUBLE PRECISION DEFAULT NULL, CHANGE masse_grasse_objectif masse_grasse_objectif DOUBLE PRECISION DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE objectif RENAME INDEX idx_objectif_user TO IDX_E2F86851A76ED395');
        $this->addSql('ALTER TABLE performance DROP FOREIGN KEY `performance_ibfk_1`');
        $this->addSql('ALTER TABLE performance ADD CONSTRAINT FK_82D79681A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE performance RENAME INDEX user_id TO IDX_82D79681A76ED395');
        $this->addSql('ALTER TABLE reservation CHANGE date_reservation date_reservation DATETIME NOT NULL');
        $this->addSql('ALTER TABLE seance DROP FOREIGN KEY `FK_SEANCE_CLIENT`');
        $this->addSql('ALTER TABLE seance CHANGE capacite_max capacite_max INT NOT NULL');
        $this->addSql('ALTER TABLE seance ADD CONSTRAINT FK_DF7DFD0E19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE seance RENAME INDEX fk_seance_client TO IDX_DF7DFD0E19EB6921');
        $this->addSql('DROP INDEX nom ON sport');
        $this->addSql('ALTER TABLE sport DROP created_at, CHANGE description description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE must_change_password must_change_password TINYINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admin_log DROP FOREIGN KEY FK_F9383BB0B03A8386');
        $this->addSql('ALTER TABLE client_coach DROP FOREIGN KEY FK_9C34F17B19EB6921');
        $this->addSql('ALTER TABLE client_coach DROP FOREIGN KEY FK_9C34F17B3C105691');
        $this->addSql('DROP TABLE admin_log');
        $this->addSql('DROP TABLE client_coach');
        $this->addSql('DROP TABLE setting');
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF0E3797A94');
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF019EB6921');
        $this->addSql('ALTER TABLE avis RENAME INDEX idx_8f91abf0e3797a94 TO IDX_AVIS_SEANCE');
        $this->addSql('ALTER TABLE avis RENAME INDEX idx_8f91abf019eb6921 TO IDX_AVIS_CLIENT');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455AC78BCF8');
        $this->addSql('ALTER TABLE client ADD coach_id INT DEFAULT NULL, CHANGE sport_id sport_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT `FK_CLIENT_SPORT` FOREIGN KEY (sport_id) REFERENCES sport (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE client RENAME INDEX idx_c7440455ac78bcf8 TO FK_CLIENT_SPORT');
        $this->addSql('ALTER TABLE coach DROP FOREIGN KEY FK_3F596DCCAC78BCF8');
        $this->addSql('ALTER TABLE coach CHANGE sport_id sport_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE coach ADD CONSTRAINT `FK_COACH_SPORT` FOREIGN KEY (sport_id) REFERENCES sport (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE coach RENAME INDEX idx_3f596dccac78bcf8 TO FK_COACH_SPORT');
        $this->addSql('ALTER TABLE objectif CHANGE vo2_objectif vo2_objectif FLOAT DEFAULT NULL, CHANGE masse_grasse_objectif masse_grasse_objectif FLOAT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE objectif RENAME INDEX idx_e2f86851a76ed395 TO IDX_OBJECTIF_USER');
        $this->addSql('ALTER TABLE performance DROP FOREIGN KEY FK_82D79681A76ED395');
        $this->addSql('ALTER TABLE performance ADD CONSTRAINT `performance_ibfk_1` FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE performance RENAME INDEX idx_82d79681a76ed395 TO user_id');
        $this->addSql('ALTER TABLE reservation CHANGE date_reservation date_reservation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE seance DROP FOREIGN KEY FK_DF7DFD0E19EB6921');
        $this->addSql('ALTER TABLE seance CHANGE capacite_max capacite_max INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE seance ADD CONSTRAINT `FK_SEANCE_CLIENT` FOREIGN KEY (client_id) REFERENCES client (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE seance RENAME INDEX idx_df7dfd0e19eb6921 TO FK_SEANCE_CLIENT');
        $this->addSql('ALTER TABLE sport ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE description description TEXT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX nom ON sport (nom)');
        $this->addSql('ALTER TABLE user CHANGE must_change_password must_change_password TINYINT DEFAULT 0 NOT NULL');
    }
}
