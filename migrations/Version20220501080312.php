<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220501080312 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE classe (id INT AUTO_INCREMENT NOT NULL, niveau_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, INDEX IDX_8F87BF96B3E9C81 (niveau_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE classe_eleve (id INT AUTO_INCREMENT NOT NULL, classe_id INT DEFAULT NULL, eleve_id INT DEFAULT NULL, date_valide DATETIME NOT NULL, date_fin DATETIME NOT NULL, INDEX IDX_A5D651CF8F5EA509 (classe_id), INDEX IDX_A5D651CFA6CC7B2 (eleve_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE eleve (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, eleve_matiere_id INT DEFAULT NULL, nom VARCHAR(255) DEFAULT NULL, prenom VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, mail VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_ECA105F7A76ED395 (user_id), INDEX IDX_ECA105F7F6563D0A (eleve_matiere_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE eleve_matiere (id INT AUTO_INCREMENT NOT NULL, principale DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE matiere (id INT AUTO_INCREMENT NOT NULL, eleve_matiere_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, INDEX IDX_9014574AF6563D0A (eleve_matiere_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE niveau (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `option` (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE option_eleve (id INT AUTO_INCREMENT NOT NULL, option_eleve_id INT DEFAULT NULL, eleve_id INT DEFAULT NULL, valide DATETIME NOT NULL, INDEX IDX_D54AB89F2A1BB616 (option_eleve_id), INDEX IDX_D54AB89FA6CC7B2 (eleve_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE parent_eleve (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, ville VARCHAR(255) NOT NULL, code_postal VARCHAR(255) NOT NULL, pays VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE parent_eleve_eleve (parent_eleve_id INT NOT NULL, eleve_id INT NOT NULL, INDEX IDX_98ED0A6E95A16B63 (parent_eleve_id), INDEX IDX_98ED0A6EA6CC7B2 (eleve_id), PRIMARY KEY(parent_eleve_id, eleve_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE classe ADD CONSTRAINT FK_8F87BF96B3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)');
        $this->addSql('ALTER TABLE classe_eleve ADD CONSTRAINT FK_A5D651CF8F5EA509 FOREIGN KEY (classe_id) REFERENCES classe (id)');
        $this->addSql('ALTER TABLE classe_eleve ADD CONSTRAINT FK_A5D651CFA6CC7B2 FOREIGN KEY (eleve_id) REFERENCES eleve (id)');
        $this->addSql('ALTER TABLE eleve ADD CONSTRAINT FK_ECA105F7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE eleve ADD CONSTRAINT FK_ECA105F7F6563D0A FOREIGN KEY (eleve_matiere_id) REFERENCES eleve_matiere (id)');
        $this->addSql('ALTER TABLE matiere ADD CONSTRAINT FK_9014574AF6563D0A FOREIGN KEY (eleve_matiere_id) REFERENCES eleve_matiere (id)');
        $this->addSql('ALTER TABLE option_eleve ADD CONSTRAINT FK_D54AB89F2A1BB616 FOREIGN KEY (option_eleve_id) REFERENCES `option` (id)');
        $this->addSql('ALTER TABLE option_eleve ADD CONSTRAINT FK_D54AB89FA6CC7B2 FOREIGN KEY (eleve_id) REFERENCES eleve (id)');
        $this->addSql('ALTER TABLE parent_eleve_eleve ADD CONSTRAINT FK_98ED0A6E95A16B63 FOREIGN KEY (parent_eleve_id) REFERENCES parent_eleve (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE parent_eleve_eleve ADD CONSTRAINT FK_98ED0A6EA6CC7B2 FOREIGN KEY (eleve_id) REFERENCES eleve (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE classe_eleve DROP FOREIGN KEY FK_A5D651CF8F5EA509');
        $this->addSql('ALTER TABLE classe_eleve DROP FOREIGN KEY FK_A5D651CFA6CC7B2');
        $this->addSql('ALTER TABLE option_eleve DROP FOREIGN KEY FK_D54AB89FA6CC7B2');
        $this->addSql('ALTER TABLE parent_eleve_eleve DROP FOREIGN KEY FK_98ED0A6EA6CC7B2');
        $this->addSql('ALTER TABLE eleve DROP FOREIGN KEY FK_ECA105F7F6563D0A');
        $this->addSql('ALTER TABLE matiere DROP FOREIGN KEY FK_9014574AF6563D0A');
        $this->addSql('ALTER TABLE classe DROP FOREIGN KEY FK_8F87BF96B3E9C81');
        $this->addSql('ALTER TABLE option_eleve DROP FOREIGN KEY FK_D54AB89F2A1BB616');
        $this->addSql('ALTER TABLE parent_eleve_eleve DROP FOREIGN KEY FK_98ED0A6E95A16B63');
        $this->addSql('ALTER TABLE eleve DROP FOREIGN KEY FK_ECA105F7A76ED395');
        $this->addSql('DROP TABLE classe');
        $this->addSql('DROP TABLE classe_eleve');
        $this->addSql('DROP TABLE eleve');
        $this->addSql('DROP TABLE eleve_matiere');
        $this->addSql('DROP TABLE matiere');
        $this->addSql('DROP TABLE niveau');
        $this->addSql('DROP TABLE `option`');
        $this->addSql('DROP TABLE option_eleve');
        $this->addSql('DROP TABLE parent_eleve');
        $this->addSql('DROP TABLE parent_eleve_eleve');
        $this->addSql('DROP TABLE user');
    }
}
