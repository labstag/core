<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251214142849 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification (id CHAR(36) NOT NULL, message LONGTEXT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, refuser_id CHAR(36) DEFAULT NULL, INDEX IDX_BF5476CA2B445CEF (refuser_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA2B445CEF FOREIGN KEY (refuser_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE configuration ADD game_placeholder VARCHAR(255) DEFAULT NULL, ADD region_tmdb VARCHAR(255) DEFAULT NULL, ADD defaultuser_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE configuration ADD CONSTRAINT FK_A5E2A5D7B90C3F45 FOREIGN KEY (defaultuser_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_A5E2A5D7B90C3F45 ON configuration (defaultuser_id)');
        $this->addSql('ALTER TABLE episode DROP FOREIGN KEY `FK_DDAA1CDAE6178236`');
        $this->addSql('ALTER TABLE episode ADD CONSTRAINT FK_DDAA1CDAE6178236 FOREIGN KEY (refseason_id) REFERENCES season (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game ADD enable TINYINT DEFAULT 1 NOT NULL, ADD summary LONGTEXT DEFAULT NULL, ADD meta_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318C39FCA6F9 FOREIGN KEY (meta_id) REFERENCES meta (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_232B318C39FCA6F9 ON game (meta_id)');
        $this->addSql('ALTER TABLE paragraph ADD game_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD39862E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('CREATE INDEX IDX_7DD39862E48FD905 ON paragraph (game_id)');
        $this->addSql('ALTER TABLE season DROP FOREIGN KEY `FK_F0E45BA9691F3A10`');
        $this->addSql('ALTER TABLE season ADD backdrop VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE season ADD CONSTRAINT FK_F0E45BA9691F3A10 FOREIGN KEY (refserie_id) REFERENCES serie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rememberme_token CHANGE class class VARCHAR(100) DEFAULT \'\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA2B445CEF');
        $this->addSql('DROP TABLE notification');
        $this->addSql('ALTER TABLE configuration DROP FOREIGN KEY FK_A5E2A5D7B90C3F45');
        $this->addSql('DROP INDEX IDX_A5E2A5D7B90C3F45 ON configuration');
        $this->addSql('ALTER TABLE configuration DROP game_placeholder, DROP region_tmdb, DROP defaultuser_id');
        $this->addSql('ALTER TABLE episode DROP FOREIGN KEY FK_DDAA1CDAE6178236');
        $this->addSql('ALTER TABLE episode ADD CONSTRAINT `FK_DDAA1CDAE6178236` FOREIGN KEY (refseason_id) REFERENCES season (id)');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318C39FCA6F9');
        $this->addSql('DROP INDEX UNIQ_232B318C39FCA6F9 ON game');
        $this->addSql('ALTER TABLE game DROP enable, DROP summary, DROP meta_id');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD39862E48FD905');
        $this->addSql('DROP INDEX IDX_7DD39862E48FD905 ON paragraph');
        $this->addSql('ALTER TABLE paragraph DROP game_id');
        $this->addSql('ALTER TABLE rememberme_token CHANGE class class VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE season DROP FOREIGN KEY FK_F0E45BA9691F3A10');
        $this->addSql('ALTER TABLE season DROP backdrop');
        $this->addSql('ALTER TABLE season ADD CONSTRAINT `FK_F0E45BA9691F3A10` FOREIGN KEY (refserie_id) REFERENCES serie (id) ON DELETE SET NULL');
    }
}
