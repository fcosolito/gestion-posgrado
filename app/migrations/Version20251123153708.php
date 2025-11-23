<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251123153708 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pago DROP INDEX IDX_F4DF5F3E25662B3A, ADD UNIQUE INDEX UNIQ_F4DF5F3E25662B3A (comprobante_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pago DROP INDEX UNIQ_F4DF5F3E25662B3A, ADD INDEX IDX_F4DF5F3E25662B3A (comprobante_id)');
    }
}
