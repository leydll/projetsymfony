<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251017141921 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE emprunt DROP date_retour');
        $this->addSql('ALTER TABLE livre DROP FOREIGN KEY livre_ibfk_1');
        $this->addSql('DROP INDEX auteur_id ON livre');
        $this->addSql('ALTER TABLE livre ADD disponible VARCHAR(20) NOT NULL, DROP auteur_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE emprunt ADD date_retour DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE livre ADD auteur_id INT DEFAULT NULL, DROP disponible');
        $this->addSql('ALTER TABLE livre ADD CONSTRAINT livre_ibfk_1 FOREIGN KEY (auteur_id) REFERENCES auteur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX auteur_id ON livre (auteur_id)');
    }
}
