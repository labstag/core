<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251115195603 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE movie ADD slug VARCHAR(255) DEFAULT NULL, ADD meta_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE movie ADD CONSTRAINT FK_1D5EF26F39FCA6F9 FOREIGN KEY (meta_id) REFERENCES meta (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1D5EF26F989D9B62 ON movie (slug)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1D5EF26F39FCA6F9 ON movie (meta_id)');
        $this->addSql('CREATE INDEX IDX_MOVIE_SLUG ON movie (slug)');
        $this->addSql('ALTER TABLE paragraph ADD movie_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD398628F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id)');
        $this->addSql('CREATE INDEX IDX_7DD398628F93B6FC ON paragraph (movie_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE movie DROP FOREIGN KEY FK_1D5EF26F39FCA6F9');
        $this->addSql('DROP INDEX UNIQ_1D5EF26F989D9B62 ON movie');
        $this->addSql('DROP INDEX UNIQ_1D5EF26F39FCA6F9 ON movie');
        $this->addSql('DROP INDEX IDX_MOVIE_SLUG ON movie');
        $this->addSql('ALTER TABLE movie DROP slug, DROP meta_id');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD398628F93B6FC');
        $this->addSql('DROP INDEX IDX_7DD398628F93B6FC ON paragraph');
        $this->addSql('ALTER TABLE paragraph DROP movie_id');
    }
}
