<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251123211524 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE alumno (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, apellido VARCHAR(255) NOT NULL, dni INT NOT NULL, email VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE carrera (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, nro_ordenanza INT NOT NULL, nro_implementacion INT NOT NULL, cantidad_cuotas INT DEFAULT NULL, precio_inscripcion DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comprobante (id INT AUTO_INCREMENT NOT NULL, archivo VARCHAR(255) DEFAULT NULL, mime_type VARCHAR(255) DEFAULT NULL, tamano INT DEFAULT NULL, fecha_actualizacion DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cuota (id INT AUTO_INCREMENT NOT NULL, inscripcion_carrera_id INT DEFAULT NULL, inscripcion_edicion_id INT DEFAULT NULL, numero_cuota INT NOT NULL, INDEX IDX_763CCB0FAD8A9CE (inscripcion_carrera_id), INDEX IDX_763CCB0F1AF8A5DF (inscripcion_edicion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE curso (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, horas INT NOT NULL, nro_ordenanza INT NOT NULL, nro_implementacion INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE descuento (id INT AUTO_INCREMENT NOT NULL, descripcion LONGTEXT DEFAULT NULL, valor DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dicta (id INT AUTO_INCREMENT NOT NULL, edicion_id INT NOT NULL, docente_id INT NOT NULL, es_firmante TINYINT(1) NOT NULL, INDEX IDX_D7C9065CD651B81E (edicion_id), INDEX IDX_D7C9065C94E27525 (docente_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE docente (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, apellido VARCHAR(255) NOT NULL, dni INT NOT NULL, email VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE documentacion_nota (id INT AUTO_INCREMENT NOT NULL, archivo VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE edicion (id INT AUTO_INCREMENT NOT NULL, curso_id INT NOT NULL, precio DOUBLE PRECISION DEFAULT NULL, fecha_inicio DATE NOT NULL, fecha_fin DATE DEFAULT NULL, nombre VARCHAR(255) NOT NULL, INDEX IDX_655F773987CB4A1F (curso_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inscripcion_carrera (id INT AUTO_INCREMENT NOT NULL, descuento_id INT DEFAULT NULL, carrera_id INT NOT NULL, alumno_id INT NOT NULL, nro_legajo INT DEFAULT NULL, fecha_inscripcion DATE DEFAULT NULL, INDEX IDX_9E4F3FAFF045077C (descuento_id), INDEX IDX_9E4F3FAFC671B40F (carrera_id), INDEX IDX_9E4F3FAFFC28E5EE (alumno_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inscripcion_edicion (id INT AUTO_INCREMENT NOT NULL, descuento_id INT DEFAULT NULL, alumno_id INT NOT NULL, edicion_id INT NOT NULL, nota_id INT DEFAULT NULL, nro_legajo INT DEFAULT NULL, fecha_inscripcion DATE DEFAULT NULL, INDEX IDX_340E85A6F045077C (descuento_id), INDEX IDX_340E85A6FC28E5EE (alumno_id), INDEX IDX_340E85A6D651B81E (edicion_id), UNIQUE INDEX UNIQ_340E85A6A98F9F02 (nota_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE nota (id INT AUTO_INCREMENT NOT NULL, inscripcion_edicion_id INT NOT NULL, documentacion_nota_id INT DEFAULT NULL, valor DOUBLE PRECISION NOT NULL, descripcion LONGTEXT DEFAULT NULL, fecha_carga DATE NOT NULL, INDEX IDX_C8D03E0D1AF8A5DF (inscripcion_edicion_id), UNIQUE INDEX UNIQ_C8D03E0D5352B419 (documentacion_nota_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pago (id INT AUTO_INCREMENT NOT NULL, comprobante_id INT DEFAULT NULL, monto DOUBLE PRECISION NOT NULL, fecha_pago DATE NOT NULL, UNIQUE INDEX UNIQ_F4DF5F3E25662B3A (comprobante_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pago_cuota (id INT AUTO_INCREMENT NOT NULL, cuota_id INT NOT NULL, pago_id INT NOT NULL, monto_cuota DOUBLE PRECISION NOT NULL, INDEX IDX_B4CD6B976A7CF079 (cuota_id), INDEX IDX_B4CD6B9763FB8380 (pago_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pertenece_a (id INT AUTO_INCREMENT NOT NULL, carrera_id INT NOT NULL, curso_id INT NOT NULL, es_electivo TINYINT(1) NOT NULL, INDEX IDX_8E9FEC34C671B40F (carrera_id), INDEX IDX_8E9FEC3487CB4A1F (curso_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE precio_carrera (id INT AUTO_INCREMENT NOT NULL, carrera_id INT NOT NULL, precio DOUBLE PRECISION NOT NULL, fecha_vigencia DATE NOT NULL, fecha_creacion DATETIME NOT NULL, INDEX IDX_E8E77A87C671B40F (carrera_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cuota ADD CONSTRAINT FK_763CCB0FAD8A9CE FOREIGN KEY (inscripcion_carrera_id) REFERENCES inscripcion_carrera (id)');
        $this->addSql('ALTER TABLE cuota ADD CONSTRAINT FK_763CCB0F1AF8A5DF FOREIGN KEY (inscripcion_edicion_id) REFERENCES inscripcion_edicion (id)');
        $this->addSql('ALTER TABLE dicta ADD CONSTRAINT FK_D7C9065CD651B81E FOREIGN KEY (edicion_id) REFERENCES edicion (id)');
        $this->addSql('ALTER TABLE dicta ADD CONSTRAINT FK_D7C9065C94E27525 FOREIGN KEY (docente_id) REFERENCES docente (id)');
        $this->addSql('ALTER TABLE edicion ADD CONSTRAINT FK_655F773987CB4A1F FOREIGN KEY (curso_id) REFERENCES curso (id)');
        $this->addSql('ALTER TABLE inscripcion_carrera ADD CONSTRAINT FK_9E4F3FAFF045077C FOREIGN KEY (descuento_id) REFERENCES descuento (id)');
        $this->addSql('ALTER TABLE inscripcion_carrera ADD CONSTRAINT FK_9E4F3FAFC671B40F FOREIGN KEY (carrera_id) REFERENCES carrera (id)');
        $this->addSql('ALTER TABLE inscripcion_carrera ADD CONSTRAINT FK_9E4F3FAFFC28E5EE FOREIGN KEY (alumno_id) REFERENCES alumno (id)');
        $this->addSql('ALTER TABLE inscripcion_edicion ADD CONSTRAINT FK_340E85A6F045077C FOREIGN KEY (descuento_id) REFERENCES descuento (id)');
        $this->addSql('ALTER TABLE inscripcion_edicion ADD CONSTRAINT FK_340E85A6FC28E5EE FOREIGN KEY (alumno_id) REFERENCES alumno (id)');
        $this->addSql('ALTER TABLE inscripcion_edicion ADD CONSTRAINT FK_340E85A6D651B81E FOREIGN KEY (edicion_id) REFERENCES edicion (id)');
        $this->addSql('ALTER TABLE inscripcion_edicion ADD CONSTRAINT FK_340E85A6A98F9F02 FOREIGN KEY (nota_id) REFERENCES nota (id)');
        $this->addSql('ALTER TABLE nota ADD CONSTRAINT FK_C8D03E0D1AF8A5DF FOREIGN KEY (inscripcion_edicion_id) REFERENCES inscripcion_edicion (id)');
        $this->addSql('ALTER TABLE nota ADD CONSTRAINT FK_C8D03E0D5352B419 FOREIGN KEY (documentacion_nota_id) REFERENCES documentacion_nota (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE pago ADD CONSTRAINT FK_F4DF5F3E25662B3A FOREIGN KEY (comprobante_id) REFERENCES comprobante (id)');
        $this->addSql('ALTER TABLE pago_cuota ADD CONSTRAINT FK_B4CD6B976A7CF079 FOREIGN KEY (cuota_id) REFERENCES cuota (id)');
        $this->addSql('ALTER TABLE pago_cuota ADD CONSTRAINT FK_B4CD6B9763FB8380 FOREIGN KEY (pago_id) REFERENCES pago (id)');
        $this->addSql('ALTER TABLE pertenece_a ADD CONSTRAINT FK_8E9FEC34C671B40F FOREIGN KEY (carrera_id) REFERENCES carrera (id)');
        $this->addSql('ALTER TABLE pertenece_a ADD CONSTRAINT FK_8E9FEC3487CB4A1F FOREIGN KEY (curso_id) REFERENCES curso (id)');
        $this->addSql('ALTER TABLE precio_carrera ADD CONSTRAINT FK_E8E77A87C671B40F FOREIGN KEY (carrera_id) REFERENCES carrera (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cuota DROP FOREIGN KEY FK_763CCB0FAD8A9CE');
        $this->addSql('ALTER TABLE cuota DROP FOREIGN KEY FK_763CCB0F1AF8A5DF');
        $this->addSql('ALTER TABLE dicta DROP FOREIGN KEY FK_D7C9065CD651B81E');
        $this->addSql('ALTER TABLE dicta DROP FOREIGN KEY FK_D7C9065C94E27525');
        $this->addSql('ALTER TABLE edicion DROP FOREIGN KEY FK_655F773987CB4A1F');
        $this->addSql('ALTER TABLE inscripcion_carrera DROP FOREIGN KEY FK_9E4F3FAFF045077C');
        $this->addSql('ALTER TABLE inscripcion_carrera DROP FOREIGN KEY FK_9E4F3FAFC671B40F');
        $this->addSql('ALTER TABLE inscripcion_carrera DROP FOREIGN KEY FK_9E4F3FAFFC28E5EE');
        $this->addSql('ALTER TABLE inscripcion_edicion DROP FOREIGN KEY FK_340E85A6F045077C');
        $this->addSql('ALTER TABLE inscripcion_edicion DROP FOREIGN KEY FK_340E85A6FC28E5EE');
        $this->addSql('ALTER TABLE inscripcion_edicion DROP FOREIGN KEY FK_340E85A6D651B81E');
        $this->addSql('ALTER TABLE inscripcion_edicion DROP FOREIGN KEY FK_340E85A6A98F9F02');
        $this->addSql('ALTER TABLE nota DROP FOREIGN KEY FK_C8D03E0D1AF8A5DF');
        $this->addSql('ALTER TABLE nota DROP FOREIGN KEY FK_C8D03E0D5352B419');
        $this->addSql('ALTER TABLE pago DROP FOREIGN KEY FK_F4DF5F3E25662B3A');
        $this->addSql('ALTER TABLE pago_cuota DROP FOREIGN KEY FK_B4CD6B976A7CF079');
        $this->addSql('ALTER TABLE pago_cuota DROP FOREIGN KEY FK_B4CD6B9763FB8380');
        $this->addSql('ALTER TABLE pertenece_a DROP FOREIGN KEY FK_8E9FEC34C671B40F');
        $this->addSql('ALTER TABLE pertenece_a DROP FOREIGN KEY FK_8E9FEC3487CB4A1F');
        $this->addSql('ALTER TABLE precio_carrera DROP FOREIGN KEY FK_E8E77A87C671B40F');
        $this->addSql('DROP TABLE alumno');
        $this->addSql('DROP TABLE carrera');
        $this->addSql('DROP TABLE comprobante');
        $this->addSql('DROP TABLE cuota');
        $this->addSql('DROP TABLE curso');
        $this->addSql('DROP TABLE descuento');
        $this->addSql('DROP TABLE dicta');
        $this->addSql('DROP TABLE docente');
        $this->addSql('DROP TABLE documentacion_nota');
        $this->addSql('DROP TABLE edicion');
        $this->addSql('DROP TABLE inscripcion_carrera');
        $this->addSql('DROP TABLE inscripcion_edicion');
        $this->addSql('DROP TABLE nota');
        $this->addSql('DROP TABLE pago');
        $this->addSql('DROP TABLE pago_cuota');
        $this->addSql('DROP TABLE pertenece_a');
        $this->addSql('DROP TABLE precio_carrera');
    }
}
