<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251017124536 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE livre ADD livre_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE livre ADD CONSTRAINT FK_AC634F99EC470631 FOREIGN KEY (livre_id_id) REFERENCES auteur (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AC634F99EC470631 ON livre (livre_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE livre DROP FOREIGN KEY FK_AC634F99EC470631');
        $this->addSql('DROP INDEX UNIQ_AC634F99EC470631 ON livre');
        $this->addSql('ALTER TABLE livre DROP livre_id_id');
    }
}
