<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251015173644 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category_serie (category_id CHAR(36) NOT NULL, serie_id CHAR(36) NOT NULL, INDEX IDX_4209DC7D12469DE2 (category_id), INDEX IDX_4209DC7DD94388BD (serie_id), PRIMARY KEY (category_id, serie_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE season (air_date DATE DEFAULT NULL, id CHAR(36) NOT NULL, img VARCHAR(255) DEFAULT NULL, number INT DEFAULT NULL, overview LONGTEXT DEFAULT NULL, tmdb VARCHAR(255) DEFAULT NULL, vote_average DOUBLE PRECISION DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, refserie_id CHAR(36) DEFAULT NULL, INDEX IDX_F0E45BA9691F3A10 (refserie_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE serie (adult TINYINT(1) NOT NULL, certification VARCHAR(255) DEFAULT NULL, citation VARCHAR(255) DEFAULT NULL, countries JSON DEFAULT NULL, description LONGTEXT DEFAULT NULL, enable TINYINT(1) DEFAULT 1 NOT NULL, evaluation DOUBLE PRECISION DEFAULT NULL, file TINYINT(1) NOT NULL, id CHAR(36) NOT NULL, imdb VARCHAR(255) NOT NULL, img VARCHAR(255) DEFAULT NULL, lastrelease_date DATE DEFAULT NULL, release_date DATE DEFAULT NULL, title VARCHAR(255) NOT NULL, tmdb VARCHAR(255) DEFAULT NULL, trailer LONGTEXT DEFAULT NULL, votes INT DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_AA3A933485489131 (imdb), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE category_serie ADD CONSTRAINT FK_4209DC7D12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_serie ADD CONSTRAINT FK_4209DC7DD94388BD FOREIGN KEY (serie_id) REFERENCES serie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE season ADD CONSTRAINT FK_F0E45BA9691F3A10 FOREIGN KEY (refserie_id) REFERENCES serie (id)');
        $this->addSql('ALTER TABLE tag_movie DROP FOREIGN KEY `FK_3FB2EB698F93B6FC`');
        $this->addSql('ALTER TABLE tag_movie DROP FOREIGN KEY `FK_3FB2EB69BAD26311`');
        $this->addSql('DROP TABLE tag_movie');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY `FK_7DD398623FCF0451`');
        $this->addSql('DROP INDEX IDX_7DD398623FCF0451 ON paragraph');
        $this->addSql('ALTER TABLE paragraph DROP refmovie_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tag_movie (tag_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, movie_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_3FB2EB69BAD26311 (tag_id), INDEX IDX_3FB2EB698F93B6FC (movie_id), PRIMARY KEY (tag_id, movie_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE tag_movie ADD CONSTRAINT `FK_3FB2EB698F93B6FC` FOREIGN KEY (movie_id) REFERENCES movie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_movie ADD CONSTRAINT `FK_3FB2EB69BAD26311` FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_serie DROP FOREIGN KEY FK_4209DC7D12469DE2');
        $this->addSql('ALTER TABLE category_serie DROP FOREIGN KEY FK_4209DC7DD94388BD');
        $this->addSql('ALTER TABLE season DROP FOREIGN KEY FK_F0E45BA9691F3A10');
        $this->addSql('DROP TABLE category_serie');
        $this->addSql('DROP TABLE season');
        $this->addSql('DROP TABLE serie');
        $this->addSql('ALTER TABLE paragraph ADD refmovie_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT `FK_7DD398623FCF0451` FOREIGN KEY (refmovie_id) REFERENCES movie (id)');
        $this->addSql('CREATE INDEX IDX_7DD398623FCF0451 ON paragraph (refmovie_id)');
    }
}
