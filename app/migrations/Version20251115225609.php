<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251115225609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE descuento (id INT AUTO_INCREMENT NOT NULL, descripcion LONGTEXT DEFAULT NULL, valor DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE inscripcion_carrera ADD descuento_id INT DEFAULT NULL, DROP descuento');
        $this->addSql('ALTER TABLE inscripcion_carrera ADD CONSTRAINT FK_9E4F3FAFF045077C FOREIGN KEY (descuento_id) REFERENCES descuento (id)');
        $this->addSql('CREATE INDEX IDX_9E4F3FAFF045077C ON inscripcion_carrera (descuento_id)');
        $this->addSql('ALTER TABLE inscripcion_edicion ADD descuento_id INT DEFAULT NULL, ADD nota_id INT DEFAULT NULL, DROP descuento');
        $this->addSql('ALTER TABLE inscripcion_edicion ADD CONSTRAINT FK_340E85A6F045077C FOREIGN KEY (descuento_id) REFERENCES descuento (id)');
        $this->addSql('ALTER TABLE inscripcion_edicion ADD CONSTRAINT FK_340E85A6A98F9F02 FOREIGN KEY (nota_id) REFERENCES nota (id)');
        $this->addSql('CREATE INDEX IDX_340E85A6F045077C ON inscripcion_edicion (descuento_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_340E85A6A98F9F02 ON inscripcion_edicion (nota_id)');
        $this->addSql('ALTER TABLE pago ADD monto DOUBLE PRECISION NOT NULL, DROP monto_pagado, DROP monto_cuota');
        $this->addSql('ALTER TABLE pago_cuota ADD monto_cuota DOUBLE PRECISION NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inscripcion_carrera DROP FOREIGN KEY FK_9E4F3FAFF045077C');
        $this->addSql('ALTER TABLE inscripcion_edicion DROP FOREIGN KEY FK_340E85A6F045077C');
        $this->addSql('DROP TABLE descuento');
        $this->addSql('DROP INDEX IDX_9E4F3FAFF045077C ON inscripcion_carrera');
        $this->addSql('ALTER TABLE inscripcion_carrera ADD descuento DOUBLE PRECISION NOT NULL, DROP descuento_id');
        $this->addSql('ALTER TABLE inscripcion_edicion DROP FOREIGN KEY FK_340E85A6A98F9F02');
        $this->addSql('DROP INDEX IDX_340E85A6F045077C ON inscripcion_edicion');
        $this->addSql('DROP INDEX UNIQ_340E85A6A98F9F02 ON inscripcion_edicion');
        $this->addSql('ALTER TABLE inscripcion_edicion ADD descuento DOUBLE PRECISION NOT NULL, DROP descuento_id, DROP nota_id');
        $this->addSql('ALTER TABLE pago ADD monto_cuota DOUBLE PRECISION NOT NULL, CHANGE monto monto_pagado DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE pago_cuota DROP monto_cuota');
    }
}
