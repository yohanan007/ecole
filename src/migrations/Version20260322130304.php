<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260322130304 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `admin` (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, telephone VARCHAR(255) DEFAULT NULL, is_active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_880E0D76A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `admin` ADD CONSTRAINT FK_880E0D76A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE eleve ADD validated_by_admin_id INT DEFAULT NULL, ADD is_validated TINYINT(1) NOT NULL, ADD validated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE eleve ADD CONSTRAINT FK_ECA105F77C233EA9 FOREIGN KEY (validated_by_admin_id) REFERENCES `admin` (id)');
        $this->addSql('CREATE INDEX IDX_ECA105F77C233EA9 ON eleve (validated_by_admin_id)');
        $this->addSql('ALTER TABLE evenement ADD admin_createur_id INT DEFAULT NULL, ADD created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681EEEB848EC FOREIGN KEY (admin_createur_id) REFERENCES `admin` (id)');
        $this->addSql('CREATE INDEX IDX_B26681EEEB848EC ON evenement (admin_createur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE eleve DROP FOREIGN KEY FK_ECA105F77C233EA9');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681EEEB848EC');
        $this->addSql('ALTER TABLE `admin` DROP FOREIGN KEY FK_880E0D76A76ED395');
        $this->addSql('DROP TABLE `admin`');
        $this->addSql('DROP INDEX IDX_ECA105F77C233EA9 ON eleve');
        $this->addSql('ALTER TABLE eleve DROP validated_by_admin_id, DROP is_validated, DROP validated_at');
        $this->addSql('DROP INDEX IDX_B26681EEEB848EC ON evenement');
        $this->addSql('ALTER TABLE evenement DROP admin_createur_id, DROP created_at');
    }
}
