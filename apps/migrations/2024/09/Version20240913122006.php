<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240913122006 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE chapter ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE edito ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE file ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE history ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE memo ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE meta ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE page ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE tag ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD deleted_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE edito DROP deleted_at');
        $this->addSql('ALTER TABLE chapter DROP deleted_at');
        $this->addSql('ALTER TABLE memo DROP deleted_at');
        $this->addSql('ALTER TABLE post DROP deleted_at');
        $this->addSql('ALTER TABLE tag DROP deleted_at');
        $this->addSql('ALTER TABLE user DROP deleted_at');
        $this->addSql('ALTER TABLE meta DROP deleted_at');
        $this->addSql('ALTER TABLE history DROP deleted_at');
        $this->addSql('ALTER TABLE category DROP deleted_at');
        $this->addSql('ALTER TABLE file DROP deleted_at');
        $this->addSql('ALTER TABLE page DROP deleted_at');
    }
}
