<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240103143036 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, sujet VARCHAR(255) DEFAULT NULL, corps VARCHAR(255) DEFAULT NULL, lieu VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evenement_agenda (evenement_id INT NOT NULL, agenda_id INT NOT NULL, INDEX IDX_9F1818F5FD02F13 (evenement_id), INDEX IDX_9F1818F5EA67784A (agenda_id), PRIMARY KEY(evenement_id, agenda_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE evenement_agenda ADD CONSTRAINT FK_9F1818F5FD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evenement_agenda ADD CONSTRAINT FK_9F1818F5EA67784A FOREIGN KEY (agenda_id) REFERENCES agenda (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE eleve ADD user_id INT DEFAULT NULL, DROP mail');
        $this->addSql('ALTER TABLE eleve ADD CONSTRAINT FK_ECA105F7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_ECA105F7A76ED395 ON eleve (user_id)');
        $this->addSql('ALTER TABLE parent_eleve ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE parent_eleve ADD CONSTRAINT FK_20909154A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_20909154A76ED395 ON parent_eleve (user_id)');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649A6CC7B2');
        $this->addSql('DROP INDEX UNIQ_8D93D649A6CC7B2 ON user');
        $this->addSql('ALTER TABLE user DROP eleve_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evenement_agenda DROP FOREIGN KEY FK_9F1818F5FD02F13');
        $this->addSql('ALTER TABLE evenement_agenda DROP FOREIGN KEY FK_9F1818F5EA67784A');
        $this->addSql('DROP TABLE evenement');
        $this->addSql('DROP TABLE evenement_agenda');
        $this->addSql('ALTER TABLE user ADD eleve_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649A6CC7B2 FOREIGN KEY (eleve_id) REFERENCES eleve (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649A6CC7B2 ON user (eleve_id)');
        $this->addSql('ALTER TABLE eleve DROP FOREIGN KEY FK_ECA105F7A76ED395');
        $this->addSql('DROP INDEX UNIQ_ECA105F7A76ED395 ON eleve');
        $this->addSql('ALTER TABLE eleve ADD mail VARCHAR(255) NOT NULL, DROP user_id');
        $this->addSql('ALTER TABLE parent_eleve DROP FOREIGN KEY FK_20909154A76ED395');
        $this->addSql('DROP INDEX UNIQ_20909154A76ED395 ON parent_eleve');
        $this->addSql('ALTER TABLE parent_eleve DROP user_id');
    }
}
