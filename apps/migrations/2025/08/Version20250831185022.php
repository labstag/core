<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250831185022 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE saga (id CHAR(36) NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE movie ADD saga_id CHAR(36) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE movie ADD CONSTRAINT FK_1D5EF26FB2CCEE2E FOREIGN KEY (saga_id) REFERENCES saga (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1D5EF26FB2CCEE2E ON movie (saga_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE saga
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE movie DROP FOREIGN KEY FK_1D5EF26FB2CCEE2E
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_1D5EF26FB2CCEE2E ON movie
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE movie DROP saga_id
        SQL);
    }
}
