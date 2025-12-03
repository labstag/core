<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251203164228 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game ADD enable TINYINT(1) DEFAULT 1 NOT NULL, ADD meta_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318C39FCA6F9 FOREIGN KEY (meta_id) REFERENCES meta (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_232B318C39FCA6F9 ON game (meta_id)');
        $this->addSql('ALTER TABLE paragraph ADD game_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD39862E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('CREATE INDEX IDX_7DD39862E48FD905 ON paragraph (game_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318C39FCA6F9');
        $this->addSql('DROP INDEX UNIQ_232B318C39FCA6F9 ON game');
        $this->addSql('ALTER TABLE game DROP enable, DROP meta_id');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD39862E48FD905');
        $this->addSql('DROP INDEX IDX_7DD39862E48FD905 ON paragraph');
        $this->addSql('ALTER TABLE paragraph DROP game_id');
    }
}
