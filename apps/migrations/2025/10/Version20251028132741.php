<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251028132741 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chapter CHANGE meta_id meta_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE page CHANGE meta_id meta_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE paragraph ADD saga_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD39862B2CCEE2E FOREIGN KEY (saga_id) REFERENCES saga (id)');
        $this->addSql('CREATE INDEX IDX_7DD39862B2CCEE2E ON paragraph (saga_id)');
        $this->addSql('ALTER TABLE post CHANGE meta_id meta_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE saga ADD enable TINYINT(1) DEFAULT 1 NOT NULL, ADD meta_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE saga ADD CONSTRAINT FK_1D2DDD739FCA6F9 FOREIGN KEY (meta_id) REFERENCES meta (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1D2DDD739FCA6F9 ON saga (meta_id)');
        $this->addSql('ALTER TABLE season CHANGE meta_id meta_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE serie CHANGE meta_id meta_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE story CHANGE meta_id meta_id CHAR(36) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chapter CHANGE meta_id meta_id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE page CHANGE meta_id meta_id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD39862B2CCEE2E');
        $this->addSql('DROP INDEX IDX_7DD39862B2CCEE2E ON paragraph');
        $this->addSql('ALTER TABLE paragraph DROP saga_id');
        $this->addSql('ALTER TABLE post CHANGE meta_id meta_id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE saga DROP FOREIGN KEY FK_1D2DDD739FCA6F9');
        $this->addSql('DROP INDEX UNIQ_1D2DDD739FCA6F9 ON saga');
        $this->addSql('ALTER TABLE saga DROP enable, DROP meta_id');
        $this->addSql('ALTER TABLE season CHANGE meta_id meta_id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE serie CHANGE meta_id meta_id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE story CHANGE meta_id meta_id CHAR(36) NOT NULL');
    }
}
