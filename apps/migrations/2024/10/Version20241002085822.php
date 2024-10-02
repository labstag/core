<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241002085822 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, ADD avatar_name VARCHAR(255) DEFAULT NULL, ADD avatar_original_name VARCHAR(255) DEFAULT NULL, ADD avatar_mime_type VARCHAR(255) DEFAULT NULL, ADD avatar_size INT DEFAULT NULL, ADD avatar_dimensions LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP created_at, DROP updated_at, DROP avatar_name, DROP avatar_original_name, DROP avatar_mime_type, DROP avatar_size, DROP avatar_dimensions');
    }
}
