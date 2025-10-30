<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251030133015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_CHAPTER_SLUG ON chapter');
        $this->addSql('DROP INDEX UNIQ_F981B52E989D9B62 ON chapter');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX IDX_CHAPTER_SLUG ON chapter (slug)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F981B52E989D9B62 ON chapter (slug)');
    }
}
