<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260503081010 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evenement ADD parent_id INT DEFAULT NULL, ADD heure_debut DATETIME DEFAULT NULL, ADD recurrence_end DATETIME DEFAULT NULL, CHANGE agenda_id agenda_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681E727ACA70 FOREIGN KEY (parent_id) REFERENCES evenement (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_B26681E727ACA70 ON evenement (parent_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681E727ACA70');
        $this->addSql('DROP INDEX IDX_B26681E727ACA70 ON evenement');
        $this->addSql('ALTER TABLE evenement DROP parent_id, DROP heure_debut, DROP recurrence_end, CHANGE agenda_id agenda_id INT NOT NULL');
    }
}
