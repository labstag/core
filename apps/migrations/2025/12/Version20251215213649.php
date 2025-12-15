<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251215213649 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recommendation DROP FOREIGN KEY `FK_433224D23EE66154`');
        $this->addSql('ALTER TABLE recommendation DROP FOREIGN KEY `FK_433224D23FCF0451`');
        $this->addSql('ALTER TABLE recommendation DROP FOREIGN KEY `FK_433224D2691F3A10`');
        $this->addSql('DROP TABLE recommendation');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE recommendation (id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, overview LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, poster VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, release_date DATE NOT NULL, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, tmdb VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, refmovie_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, refsaga_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, refserie_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_433224D23FCF0451 (refmovie_id), INDEX IDX_433224D23EE66154 (refsaga_id), INDEX IDX_433224D2691F3A10 (refserie_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE recommendation ADD CONSTRAINT `FK_433224D23EE66154` FOREIGN KEY (refsaga_id) REFERENCES saga (id)');
        $this->addSql('ALTER TABLE recommendation ADD CONSTRAINT `FK_433224D23FCF0451` FOREIGN KEY (refmovie_id) REFERENCES movie (id)');
        $this->addSql('ALTER TABLE recommendation ADD CONSTRAINT `FK_433224D2691F3A10` FOREIGN KEY (refserie_id) REFERENCES serie (id)');
    }
}
