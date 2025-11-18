<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251118154712 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE company (id CHAR(36) NOT NULL, img VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, tmdb VARCHAR(255) NOT NULL, url VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE company_movie (company_id CHAR(36) NOT NULL, movie_id CHAR(36) NOT NULL, INDEX IDX_5AAF500A979B1AD6 (company_id), INDEX IDX_5AAF500A8F93B6FC (movie_id), PRIMARY KEY (company_id, movie_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE company_serie (company_id CHAR(36) NOT NULL, serie_id CHAR(36) NOT NULL, INDEX IDX_EDCB3151979B1AD6 (company_id), INDEX IDX_EDCB3151D94388BD (serie_id), PRIMARY KEY (company_id, serie_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE company_movie ADD CONSTRAINT FK_5AAF500A979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE company_movie ADD CONSTRAINT FK_5AAF500A8F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE company_serie ADD CONSTRAINT FK_EDCB3151979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE company_serie ADD CONSTRAINT FK_EDCB3151D94388BD FOREIGN KEY (serie_id) REFERENCES serie (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company_movie DROP FOREIGN KEY FK_5AAF500A979B1AD6');
        $this->addSql('ALTER TABLE company_movie DROP FOREIGN KEY FK_5AAF500A8F93B6FC');
        $this->addSql('ALTER TABLE company_serie DROP FOREIGN KEY FK_EDCB3151979B1AD6');
        $this->addSql('ALTER TABLE company_serie DROP FOREIGN KEY FK_EDCB3151D94388BD');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE company_movie');
        $this->addSql('DROP TABLE company_serie');
    }
}
