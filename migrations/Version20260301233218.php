<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260301233218 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client DROP coach_id, CHANGE sport_id sport_id INT NOT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455AC78BCF8 FOREIGN KEY (sport_id) REFERENCES sport (id)');
        $this->addSql('ALTER TABLE client RENAME INDEX fk_client_sport TO IDX_C7440455AC78BCF8');
        $this->addSql('ALTER TABLE coach DROP FOREIGN KEY `FK_COACH_SPORT`');
        $this->addSql('ALTER TABLE coach CHANGE sport_id sport_id INT NOT NULL');
        $this->addSql('ALTER TABLE coach ADD CONSTRAINT FK_3F596DCCAC78BCF8 FOREIGN KEY (sport_id) REFERENCES sport (id)');
        $this->addSql('ALTER TABLE coach RENAME INDEX fk_coach_sport TO IDX_3F596DCCAC78BCF8');
        $this->addSql('ALTER TABLE notification CHANGE is_read is_read TINYINT NOT NULL, CHANGE created_at created_at VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE notification RENAME INDEX user_id TO IDX_BF5476CAA76ED395');
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
        $this->addSql('ALTER TABLE user DROP notifications_enabled, CHANGE must_change_password must_change_password TINYINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455AC78BCF8');
        $this->addSql('ALTER TABLE client ADD coach_id INT DEFAULT NULL, CHANGE sport_id sport_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE client RENAME INDEX idx_c7440455ac78bcf8 TO FK_CLIENT_SPORT');
        $this->addSql('ALTER TABLE coach DROP FOREIGN KEY FK_3F596DCCAC78BCF8');
        $this->addSql('ALTER TABLE coach CHANGE sport_id sport_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE coach ADD CONSTRAINT `FK_COACH_SPORT` FOREIGN KEY (sport_id) REFERENCES sport (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE coach RENAME INDEX idx_3f596dccac78bcf8 TO FK_COACH_SPORT');
        $this->addSql('ALTER TABLE notification CHANGE is_read is_read TINYINT DEFAULT 0, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE notification RENAME INDEX idx_bf5476caa76ed395 TO user_id');
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
        $this->addSql('ALTER TABLE user ADD notifications_enabled TINYINT DEFAULT 1, CHANGE must_change_password must_change_password TINYINT DEFAULT 0 NOT NULL');
    }
}
