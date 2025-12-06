<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251206160356 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE configuration ADD game_placeholder VARCHAR(255) DEFAULT NULL, ADD region_tmdb VARCHAR(255) DEFAULT NULL, ADD defaultuser_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE configuration ADD CONSTRAINT FK_A5E2A5D7B90C3F45 FOREIGN KEY (defaultuser_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_A5E2A5D7B90C3F45 ON configuration (defaultuser_id)');
        $this->addSql('ALTER TABLE season ADD backdrop VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE rememberme_token CHANGE class class VARCHAR(100) DEFAULT \'\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE configuration DROP FOREIGN KEY FK_A5E2A5D7B90C3F45');
        $this->addSql('DROP INDEX IDX_A5E2A5D7B90C3F45 ON configuration');
        $this->addSql('ALTER TABLE configuration DROP game_placeholder, DROP region_tmdb, DROP defaultuser_id');
        $this->addSql('ALTER TABLE rememberme_token CHANGE class class VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE season DROP backdrop');
    }
}
