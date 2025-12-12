<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251212112448 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification (id CHAR(36) NOT NULL, message LONGTEXT DEFAULT NULL, seen TINYINT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, refuser_id CHAR(36) DEFAULT NULL, INDEX IDX_BF5476CA2B445CEF (refuser_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA2B445CEF FOREIGN KEY (refuser_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE episode DROP FOREIGN KEY `FK_DDAA1CDAE6178236`');
        $this->addSql('ALTER TABLE episode ADD CONSTRAINT FK_DDAA1CDAE6178236 FOREIGN KEY (refseason_id) REFERENCES season (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE season DROP FOREIGN KEY `FK_F0E45BA9691F3A10`');
        $this->addSql('ALTER TABLE season ADD CONSTRAINT FK_F0E45BA9691F3A10 FOREIGN KEY (refserie_id) REFERENCES serie (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA2B445CEF');
        $this->addSql('DROP TABLE notification');
        $this->addSql('ALTER TABLE episode DROP FOREIGN KEY FK_DDAA1CDAE6178236');
        $this->addSql('ALTER TABLE episode ADD CONSTRAINT `FK_DDAA1CDAE6178236` FOREIGN KEY (refseason_id) REFERENCES season (id)');
        $this->addSql('ALTER TABLE season DROP FOREIGN KEY FK_F0E45BA9691F3A10');
        $this->addSql('ALTER TABLE season ADD CONSTRAINT `FK_F0E45BA9691F3A10` FOREIGN KEY (refserie_id) REFERENCES serie (id) ON DELETE SET NULL');
    }
}
