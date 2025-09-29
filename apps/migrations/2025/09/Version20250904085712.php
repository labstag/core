<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250904085712 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE tag_movie (tag_id CHAR(36) NOT NULL, movie_id CHAR(36) NOT NULL, INDEX IDX_3FB2EB69BAD26311 (tag_id), INDEX IDX_3FB2EB698F93B6FC (movie_id), PRIMARY KEY(tag_id, movie_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tag_movie ADD CONSTRAINT FK_3FB2EB69BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tag_movie ADD CONSTRAINT FK_3FB2EB698F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE tag_movie DROP FOREIGN KEY FK_3FB2EB69BAD26311
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tag_movie DROP FOREIGN KEY FK_3FB2EB698F93B6FC
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tag_movie
        SQL);
    }
}
