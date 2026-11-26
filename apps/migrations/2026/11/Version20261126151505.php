<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261126151505 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger (id VARCHAR(26) NOT NULL, correlation_id VARCHAR(26) NOT NULL, transport VARCHAR(64) NOT NULL, message_class VARCHAR(255) NOT NULL, status VARCHAR(16) NOT NULL, payload JSON DEFAULT NULL, headers JSON DEFAULT NULL, retry_count INT NOT NULL, dispatched_at DATETIME NOT NULL, started_at DATETIME DEFAULT NULL, finished_at DATETIME DEFAULT NULL, duration_ms INT DEFAULT NULL, exception_class VARCHAR(255) DEFAULT NULL, exception_message LONGTEXT DEFAULT NULL, expires_at DATETIME DEFAULT NULL, INDEX idx_messenger_correlation (correlation_id), INDEX idx_messenger_transport (transport), INDEX idx_messenger_message_class (message_class), INDEX idx_messenger_status (status), INDEX idx_messenger_dispatched (dispatched_at), INDEX idx_messenger_expires (expires_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger');
    }
}
