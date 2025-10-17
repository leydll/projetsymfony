<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251017145738 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE emprunt DROP date_retour');
        $this->addSql('ALTER TABLE livre DROP INDEX UNIQ_AC634F998A3C7387, ADD INDEX IDX_AC634F998A3C7387 (categorie_id_id)');
        $this->addSql('ALTER TABLE livre DROP INDEX auteur_id, ADD UNIQUE INDEX UNIQ_AC634F9960BB6FE6 (auteur_id)');
        $this->addSql('ALTER TABLE livre DROP FOREIGN KEY FK_AC634F99EC470631');
        $this->addSql('DROP INDEX UNIQ_AC634F99EC470631 ON livre');
        $this->addSql('ALTER TABLE livre ADD disponible TINYINT(1) NOT NULL, DROP livre_id_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE emprunt ADD date_retour DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE livre DROP INDEX UNIQ_AC634F9960BB6FE6, ADD INDEX auteur_id (auteur_id)');
        $this->addSql('ALTER TABLE livre DROP INDEX IDX_AC634F998A3C7387, ADD UNIQUE INDEX UNIQ_AC634F998A3C7387 (categorie_id_id)');
        $this->addSql('ALTER TABLE livre ADD livre_id_id INT DEFAULT NULL, DROP disponible');
        $this->addSql('ALTER TABLE livre ADD CONSTRAINT FK_AC634F99EC470631 FOREIGN KEY (livre_id_id) REFERENCES auteur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AC634F99EC470631 ON livre (livre_id_id)');
    }
}
