<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251123215110 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comprobante DROP mime_type, DROP tamano, DROP fecha_actualizacion');
        $this->addSql('ALTER TABLE docente ADD telefono BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE inscripcion_edicion CHANGE nro_legajo nro_legajo BIGINT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comprobante ADD mime_type VARCHAR(255) DEFAULT NULL, ADD tamano INT DEFAULT NULL, ADD fecha_actualizacion DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE inscripcion_edicion CHANGE nro_legajo nro_legajo INT DEFAULT NULL');
        $this->addSql('ALTER TABLE docente DROP telefono');
    }
}
