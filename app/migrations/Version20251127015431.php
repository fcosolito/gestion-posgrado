<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251127015431 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inscripcion_edicion DROP FOREIGN KEY FK_340E85A6A98F9F02');
        $this->addSql('DROP INDEX UNIQ_340E85A6A98F9F02 ON inscripcion_edicion');
        $this->addSql('ALTER TABLE inscripcion_edicion DROP nota_id');
        $this->addSql('ALTER TABLE nota DROP FOREIGN KEY FK_C8D03E0D1AF8A5DF');
        $this->addSql('ALTER TABLE nota ADD CONSTRAINT FK_C8D03E0D1AF8A5DF FOREIGN KEY (inscripcion_edicion_id) REFERENCES inscripcion_edicion (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inscripcion_edicion ADD nota_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE inscripcion_edicion ADD CONSTRAINT FK_340E85A6A98F9F02 FOREIGN KEY (nota_id) REFERENCES nota (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_340E85A6A98F9F02 ON inscripcion_edicion (nota_id)');
        $this->addSql('ALTER TABLE nota DROP FOREIGN KEY FK_C8D03E0D1AF8A5DF');
        $this->addSql('ALTER TABLE nota ADD CONSTRAINT FK_C8D03E0D1AF8A5DF FOREIGN KEY (inscripcion_edicion_id) REFERENCES inscripcion_edicion (id)');
    }
}
