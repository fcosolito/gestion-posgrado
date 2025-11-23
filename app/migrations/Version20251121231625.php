<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251121231625 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE nota DROP INDEX IDX_C8D03E0D5352B419, ADD UNIQUE INDEX UNIQ_C8D03E0D5352B419 (documentacion_nota_id)');
        $this->addSql('ALTER TABLE nota DROP FOREIGN KEY FK_C8D03E0D5352B419');
        $this->addSql('ALTER TABLE nota ADD CONSTRAINT FK_C8D03E0D5352B419 FOREIGN KEY (documentacion_nota_id) REFERENCES documentacion_nota (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE nota DROP INDEX UNIQ_C8D03E0D5352B419, ADD INDEX IDX_C8D03E0D5352B419 (documentacion_nota_id)');
        $this->addSql('ALTER TABLE nota DROP FOREIGN KEY FK_C8D03E0D5352B419');
        $this->addSql('ALTER TABLE nota ADD CONSTRAINT FK_C8D03E0D5352B419 FOREIGN KEY (documentacion_nota_id) REFERENCES documentacion_nota (id)');
    }
}
