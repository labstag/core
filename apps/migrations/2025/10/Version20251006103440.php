<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251006103440 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX IDX_BLOCK_SLUG ON block (slug)');
        $this->addSql('CREATE INDEX IDX_CATEGORY_TYPE_SLUG ON category (type, slug)');
        $this->addSql('CREATE INDEX IDX_CHAPTER_SLUG ON chapter (slug)');
        $this->addSql('CREATE INDEX IDX_PAGE_SLUG ON page (slug)');
        $this->addSql('CREATE INDEX IDX_POST_SLUG ON post (slug)');
        $this->addSql('CREATE INDEX IDX_SAGA_SLUG ON saga (slug)');
        $this->addSql('CREATE INDEX IDX_STORY_SLUG ON story (slug)');
        $this->addSql('CREATE INDEX IDX_TAG_TYPE_SLUG ON tag (type, slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_BLOCK_SLUG ON block');
        $this->addSql('DROP INDEX IDX_CATEGORY_TYPE_SLUG ON category');
        $this->addSql('DROP INDEX IDX_CHAPTER_SLUG ON chapter');
        $this->addSql('DROP INDEX IDX_PAGE_SLUG ON page');
        $this->addSql('DROP INDEX IDX_POST_SLUG ON post');
        $this->addSql('DROP INDEX IDX_SAGA_SLUG ON saga');
        $this->addSql('DROP INDEX IDX_STORY_SLUG ON story');
        $this->addSql('DROP INDEX IDX_TAG_TYPE_SLUG ON tag');
    }
}
