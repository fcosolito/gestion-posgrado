<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251022144440 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inscripcion_edicion ADD nota_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE inscripcion_edicion ADD CONSTRAINT FK_340E85A6A98F9F02 FOREIGN KEY (nota_id) REFERENCES nota (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_340E85A6A98F9F02 ON inscripcion_edicion (nota_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inscripcion_edicion DROP FOREIGN KEY FK_340E85A6A98F9F02');
        $this->addSql('DROP INDEX UNIQ_340E85A6A98F9F02 ON inscripcion_edicion');
        $this->addSql('ALTER TABLE inscripcion_edicion DROP nota_id');
    }
}
