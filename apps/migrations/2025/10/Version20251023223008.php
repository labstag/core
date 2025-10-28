<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251023223008 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category_serie (category_id CHAR(36) NOT NULL, serie_id CHAR(36) NOT NULL, INDEX IDX_4209DC7D12469DE2 (category_id), INDEX IDX_4209DC7DD94388BD (serie_id), PRIMARY KEY (category_id, serie_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE episode (id CHAR(36) NOT NULL, runtime INT DEFAULT NULL, overview LONGTEXT DEFAULT NULL, air_date DATE DEFAULT NULL, enable TINYINT(1) DEFAULT 1 NOT NULL, number INT NOT NULL, img VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, vote_average DOUBLE PRECISION NULL, vote_count INT NULL, tmdb VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, refseason_id CHAR(36) DEFAULT NULL, INDEX IDX_DDAA1CDAE6178236 (refseason_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE season (slug VARCHAR(255) DEFAULT NULL, air_date DATE DEFAULT NULL, id CHAR(36) NOT NULL, img VARCHAR(255) DEFAULT NULL, enable TINYINT(1) DEFAULT 1 NOT NULL, number INT DEFAULT NULL, overview LONGTEXT DEFAULT NULL, tmdb VARCHAR(255) DEFAULT NULL, vote_average DOUBLE PRECISION DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, refserie_id CHAR(36) DEFAULT NULL, meta_id CHAR(36) NOT NULL, INDEX IDX_F0E45BA9691F3A10 (refserie_id), UNIQUE INDEX UNIQ_F0E45BA939FCA6F9 (meta_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE serie (slug VARCHAR(255) DEFAULT NULL, in_production TINYINT(1) DEFAULT NULL, adult TINYINT(1) NOT NULL, certification VARCHAR(255) DEFAULT NULL, citation VARCHAR(255) DEFAULT NULL, countries JSON DEFAULT NULL, description LONGTEXT DEFAULT NULL, enable TINYINT(1) DEFAULT 1 NOT NULL, evaluation DOUBLE PRECISION DEFAULT NULL, file TINYINT(1) NOT NULL, id CHAR(36) NOT NULL, imdb VARCHAR(255) DEFAULT NULL, img VARCHAR(255) DEFAULT NULL, lastrelease_date DATE DEFAULT NULL, release_date DATE DEFAULT NULL, title VARCHAR(255) NOT NULL, tmdb VARCHAR(255) DEFAULT NULL, trailer LONGTEXT DEFAULT NULL, votes INT DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, meta_id CHAR(36) NOT NULL, UNIQUE INDEX UNIQ_AA3A9334989D9B62 (slug), UNIQUE INDEX UNIQ_AA3A933439FCA6F9 (meta_id), INDEX IDX_SERIE_SLUG (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE category_serie ADD CONSTRAINT FK_4209DC7D12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_serie ADD CONSTRAINT FK_4209DC7DD94388BD FOREIGN KEY (serie_id) REFERENCES serie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE episode ADD CONSTRAINT FK_DDAA1CDAE6178236 FOREIGN KEY (refseason_id) REFERENCES season (id)');
        $this->addSql('ALTER TABLE season ADD CONSTRAINT FK_F0E45BA9691F3A10 FOREIGN KEY (refserie_id) REFERENCES serie (id)');
        $this->addSql('ALTER TABLE season ADD CONSTRAINT FK_F0E45BA939FCA6F9 FOREIGN KEY (meta_id) REFERENCES meta (id)');
        $this->addSql('ALTER TABLE serie ADD CONSTRAINT FK_AA3A933439FCA6F9 FOREIGN KEY (meta_id) REFERENCES meta (id)');
        $this->addSql('ALTER TABLE tag_movie DROP FOREIGN KEY `FK_3FB2EB698F93B6FC`');
        $this->addSql('ALTER TABLE tag_movie DROP FOREIGN KEY `FK_3FB2EB69BAD26311`');
        $this->addSql('DROP TABLE tag_movie');
        $this->addSql('ALTER TABLE paragraph ADD season_id CHAR(36) DEFAULT NULL, ADD serie_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD39862D94388BD FOREIGN KEY (serie_id) REFERENCES serie (id)');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD398624EC001D1 FOREIGN KEY (season_id) REFERENCES season (id)');
        $this->addSql('CREATE INDEX IDX_7DD39862D94388BD ON paragraph (serie_id)');
        $this->addSql('CREATE INDEX IDX_7DD398624EC001D1 ON paragraph (season_id)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE configuration ADD chapter_placeholder VARCHAR(255) DEFAULT NULL, ADD edito_placeholder VARCHAR(255) DEFAULT NULL, ADD episode_placeholder VARCHAR(255) DEFAULT NULL, ADD language_tmdb VARCHAR(255) DEFAULT NULL, ADD memo_placeholder VARCHAR(255) DEFAULT NULL, ADD movie_placeholder VARCHAR(255) DEFAULT NULL, ADD page_placeholder VARCHAR(255) DEFAULT NULL, ADD post_placeholder VARCHAR(255) DEFAULT NULL, ADD saga_placeholder VARCHAR(255) DEFAULT NULL, ADD season_placeholder VARCHAR(255) DEFAULT NULL, ADD serie_placeholder VARCHAR(255) DEFAULT NULL, ADD star_placeholder VARCHAR(255) DEFAULT NULL, ADD story_placeholder VARCHAR(255) DEFAULT NULL, ADD user_placeholder VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE saga ADD deleted_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tag_movie (tag_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, movie_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_3FB2EB698F93B6FC (movie_id), INDEX IDX_3FB2EB69BAD26311 (tag_id), PRIMARY KEY (tag_id, movie_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE tag_movie ADD CONSTRAINT `FK_3FB2EB698F93B6FC` FOREIGN KEY (movie_id) REFERENCES movie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_movie ADD CONSTRAINT `FK_3FB2EB69BAD26311` FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_serie DROP FOREIGN KEY FK_4209DC7D12469DE2');
        $this->addSql('ALTER TABLE category_serie DROP FOREIGN KEY FK_4209DC7DD94388BD');
        $this->addSql('ALTER TABLE episode DROP FOREIGN KEY FK_DDAA1CDAE6178236');
        $this->addSql('ALTER TABLE season DROP FOREIGN KEY FK_F0E45BA9691F3A10');
        $this->addSql('ALTER TABLE season DROP FOREIGN KEY FK_F0E45BA939FCA6F9');
        $this->addSql('ALTER TABLE serie DROP FOREIGN KEY FK_AA3A933439FCA6F9');
        $this->addSql('DROP TABLE category_serie');
        $this->addSql('DROP TABLE episode');
        $this->addSql('DROP TABLE season');
        $this->addSql('DROP TABLE serie');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD39862D94388BD');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD398624EC001D1');
        $this->addSql('DROP INDEX IDX_7DD39862D94388BD ON paragraph');
        $this->addSql('DROP INDEX IDX_7DD398624EC001D1 ON paragraph');
        $this->addSql('ALTER TABLE paragraph DROP serie_id, DROP season_id');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE configuration DROP chapter_placeholder, DROP edito_placeholder, DROP episode_placeholder, DROP language_tmdb, DROP memo_placeholder, DROP movie_placeholder, DROP page_placeholder, DROP post_placeholder, DROP saga_placeholder, DROP season_placeholder, DROP serie_placeholder, DROP star_placeholder, DROP story_placeholder, DROP user_placeholder');
        $this->addSql('ALTER TABLE saga DROP deleted_at');
    }
}
