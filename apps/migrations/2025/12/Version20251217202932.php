<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251217202932 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE casting (id CHAR(36) NOT NULL, known_for_department LONGTEXT DEFAULT NULL, figure LONGTEXT DEFAULT NULL, ref_person_id CHAR(36) DEFAULT NULL, ref_season_id CHAR(36) DEFAULT NULL, ref_episode_id CHAR(36) DEFAULT NULL, ref_movie_id CHAR(36) DEFAULT NULL, ref_serie_id CHAR(36) DEFAULT NULL, INDEX IDX_D11BBA505221BA02 (ref_person_id), INDEX IDX_D11BBA503D9A0094 (ref_season_id), INDEX IDX_D11BBA5030EE8DBE (ref_episode_id), INDEX IDX_D11BBA50984707B (ref_movie_id), INDEX IDX_D11BBA505F544E3A (ref_serie_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE person (id CHAR(36) NOT NULL, slug VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, birthday DATE DEFAULT NULL, deathday DATE DEFAULT NULL, gender SMALLINT DEFAULT NULL, biography LONGTEXT DEFAULT NULL, tmdb VARCHAR(255) DEFAULT NULL, place_of_birth VARCHAR(255) DEFAULT NULL, profile VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, meta_id CHAR(36) DEFAULT NULL, UNIQUE INDEX UNIQ_34DCD17639FCA6F9 (meta_id), INDEX IDX_PERSORN_SLUG (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE casting ADD CONSTRAINT FK_D11BBA505221BA02 FOREIGN KEY (ref_person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE casting ADD CONSTRAINT FK_D11BBA503D9A0094 FOREIGN KEY (ref_season_id) REFERENCES season (id)');
        $this->addSql('ALTER TABLE casting ADD CONSTRAINT FK_D11BBA5030EE8DBE FOREIGN KEY (ref_episode_id) REFERENCES episode (id)');
        $this->addSql('ALTER TABLE casting ADD CONSTRAINT FK_D11BBA50984707B FOREIGN KEY (ref_movie_id) REFERENCES movie (id)');
        $this->addSql('ALTER TABLE casting ADD CONSTRAINT FK_D11BBA505F544E3A FOREIGN KEY (ref_serie_id) REFERENCES serie (id)');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD17639FCA6F9 FOREIGN KEY (meta_id) REFERENCES meta (id)');
        $this->addSql('ALTER TABLE configuration ADD person_placeholder VARCHAR(255) DEFAULT NULL;');
        $this->addSql('ALTER TABLE paragraph ADD person_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD39862217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('CREATE INDEX IDX_7DD39862217BBB47 ON paragraph (person_id)');
        $this->addSql('ALTER TABLE person ADD enable TINYINT DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE casting DROP FOREIGN KEY FK_D11BBA505221BA02');
        $this->addSql('ALTER TABLE casting DROP FOREIGN KEY FK_D11BBA503D9A0094');
        $this->addSql('ALTER TABLE casting DROP FOREIGN KEY FK_D11BBA5030EE8DBE');
        $this->addSql('ALTER TABLE casting DROP FOREIGN KEY FK_D11BBA50984707B');
        $this->addSql('ALTER TABLE casting DROP FOREIGN KEY FK_D11BBA505F544E3A');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD17639FCA6F9');
        $this->addSql('DROP TABLE casting');
        $this->addSql('DROP TABLE person');
        $this->addSql('ALTER TABLE configuration DROP person_placeholder');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD39862217BBB47');
        $this->addSql('DROP INDEX IDX_7DD39862217BBB47 ON paragraph');
        $this->addSql('ALTER TABLE paragraph DROP person_id');
        $this->addSql('ALTER TABLE person DROP enable');
    }
}
