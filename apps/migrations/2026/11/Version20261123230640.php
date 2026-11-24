<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261123230640 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE casting DROP FOREIGN KEY `FK_D11BBA5030EE8DBE`');
        $this->addSql('ALTER TABLE casting DROP FOREIGN KEY `FK_D11BBA503D9A0094`');
        $this->addSql('ALTER TABLE casting DROP FOREIGN KEY `FK_D11BBA505221BA02`');
        $this->addSql('ALTER TABLE casting DROP FOREIGN KEY `FK_D11BBA505F544E3A`');
        $this->addSql('ALTER TABLE casting DROP FOREIGN KEY `FK_D11BBA50984707B`');
        $this->addSql('DROP TABLE casting');
        $this->addSql('ALTER TABLE episode ADD casting JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE movie ADD casting JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE serie ADD casting JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE casting (id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, known_for_department LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, figure LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ref_person_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ref_season_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ref_episode_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ref_movie_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ref_serie_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_D11BBA5030EE8DBE (ref_episode_id), INDEX IDX_D11BBA503D9A0094 (ref_season_id), INDEX IDX_D11BBA505221BA02 (ref_person_id), INDEX IDX_D11BBA505F544E3A (ref_serie_id), INDEX IDX_D11BBA50984707B (ref_movie_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE casting ADD CONSTRAINT `FK_D11BBA5030EE8DBE` FOREIGN KEY (ref_episode_id) REFERENCES episode (id)');
        $this->addSql('ALTER TABLE casting ADD CONSTRAINT `FK_D11BBA503D9A0094` FOREIGN KEY (ref_season_id) REFERENCES season (id)');
        $this->addSql('ALTER TABLE casting ADD CONSTRAINT `FK_D11BBA505221BA02` FOREIGN KEY (ref_person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE casting ADD CONSTRAINT `FK_D11BBA505F544E3A` FOREIGN KEY (ref_serie_id) REFERENCES serie (id)');
        $this->addSql('ALTER TABLE casting ADD CONSTRAINT `FK_D11BBA50984707B` FOREIGN KEY (ref_movie_id) REFERENCES movie (id)');
        $this->addSql('ALTER TABLE episode DROP casting');
        $this->addSql('ALTER TABLE movie DROP casting');
        $this->addSql('ALTER TABLE serie DROP casting');
    }
}
