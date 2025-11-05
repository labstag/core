<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251104214724 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE media (id CHAR(36) NOT NULL, mime_type VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, size INT DEFAULT NULL, slug VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_6A2CA10C989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE tag_chapter DROP FOREIGN KEY `FK_CBB09884579F4768`');
        $this->addSql('ALTER TABLE tag_chapter DROP FOREIGN KEY `FK_CBB09884BAD26311`');
        $this->addSql('DROP TABLE tag_chapter');
        $this->addSql('DROP INDEX IDX_CATEGORY_TYPE_SLUG ON category');
        $this->addSql('CREATE INDEX IDX_CATEGORY_TYPE_SLUG ON category (slug)');
        $this->addSql('ALTER TABLE category_page DROP FOREIGN KEY `FK_9F91CC6712469DE2`');
        $this->addSql('DROP INDEX IDX_9F91CC6712469DE2 ON category_page');
        $this->addSql('ALTER TABLE category_page CHANGE category_id page_category_id CHAR(36) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (page_category_id, page_id)');
        $this->addSql('ALTER TABLE category_page ADD CONSTRAINT FK_9F91CC675FAC390 FOREIGN KEY (page_category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_9F91CC675FAC390 ON category_page (page_category_id)');
        $this->addSql('ALTER TABLE category_post DROP FOREIGN KEY `FK_D11116CA12469DE2`');
        $this->addSql('DROP INDEX IDX_D11116CA12469DE2 ON category_post');
        $this->addSql('ALTER TABLE category_post CHANGE category_id post_category_id CHAR(36) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (post_category_id, post_id)');
        $this->addSql('ALTER TABLE category_post ADD CONSTRAINT FK_D11116CAFE0617CD FOREIGN KEY (post_category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_D11116CAFE0617CD ON category_post (post_category_id)');
        $this->addSql('ALTER TABLE category_serie DROP FOREIGN KEY `FK_4209DC7D12469DE2`');
        $this->addSql('DROP INDEX IDX_4209DC7D12469DE2 ON category_serie');
        $this->addSql('ALTER TABLE category_serie CHANGE category_id serie_category_id CHAR(36) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (serie_category_id, serie_id)');
        $this->addSql('ALTER TABLE category_serie ADD CONSTRAINT FK_4209DC7D157AAFB1 FOREIGN KEY (serie_category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4209DC7D157AAFB1 ON category_serie (serie_category_id)');
        $this->addSql('ALTER TABLE category_story DROP FOREIGN KEY `FK_3654B7112469DE2`');
        $this->addSql('DROP INDEX IDX_3654B7112469DE2 ON category_story');
        $this->addSql('ALTER TABLE category_story CHANGE category_id story_category_id CHAR(36) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (story_category_id, story_id)');
        $this->addSql('ALTER TABLE category_story ADD CONSTRAINT FK_3654B71E26EF55D FOREIGN KEY (story_category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_3654B71E26EF55D ON category_story (story_category_id)');
        $this->addSql('ALTER TABLE category_movie DROP FOREIGN KEY `FK_F56DBD2612469DE2`');
        $this->addSql('DROP INDEX IDX_F56DBD2612469DE2 ON category_movie');
        $this->addSql('ALTER TABLE category_movie CHANGE category_id movie_category_id CHAR(36) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (movie_category_id, movie_id)');
        $this->addSql('ALTER TABLE category_movie ADD CONSTRAINT FK_F56DBD263DC01115 FOREIGN KEY (movie_category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_F56DBD263DC01115 ON category_movie (movie_category_id)');
        $this->addSql('ALTER TABLE paragraph ADD pdf VARCHAR(255) DEFAULT NULL, CHANGE save save TINYINT(1) DEFAULT 1');
        $this->addSql('ALTER TABLE season DROP FOREIGN KEY `FK_F0E45BA9691F3A10`');
        $this->addSql('ALTER TABLE season ADD CONSTRAINT FK_F0E45BA9691F3A10 FOREIGN KEY (refserie_id) REFERENCES serie (id) ON DELETE SET NULL');
        $this->addSql('DROP INDEX IDX_TAG_TYPE_SLUG ON tag');
        $this->addSql('CREATE INDEX IDX_TAG_TYPE_SLUG ON tag (slug)');
        $this->addSql('ALTER TABLE tag_page DROP FOREIGN KEY `FK_FA050996BAD26311`');
        $this->addSql('DROP INDEX IDX_FA050996BAD26311 ON tag_page');
        $this->addSql('ALTER TABLE tag_page CHANGE tag_id page_tag_id CHAR(36) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (page_tag_id, page_id)');
        $this->addSql('ALTER TABLE tag_page ADD CONSTRAINT FK_FA050996D504950F FOREIGN KEY (page_tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_FA050996D504950F ON tag_page (page_tag_id)');
        $this->addSql('ALTER TABLE tag_post DROP FOREIGN KEY `FK_B485D33BBAD26311`');
        $this->addSql('DROP INDEX IDX_B485D33BBAD26311 ON tag_post');
        $this->addSql('ALTER TABLE tag_post CHANGE tag_id post_tag_id CHAR(36) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (post_tag_id, post_id)');
        $this->addSql('ALTER TABLE tag_post ADD CONSTRAINT FK_B485D33B8AF08774 FOREIGN KEY (post_tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_B485D33B8AF08774 ON tag_post (post_tag_id)');
        $this->addSql('ALTER TABLE tag_story DROP FOREIGN KEY `FK_C9BA1D3EBAD26311`');
        $this->addSql('DROP INDEX IDX_C9BA1D3EBAD26311 ON tag_story');
        $this->addSql('ALTER TABLE tag_story CHANGE tag_id story_tag_id CHAR(36) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (story_tag_id, story_id)');
        $this->addSql('ALTER TABLE tag_story ADD CONSTRAINT FK_C9BA1D3EF5D1D1E3 FOREIGN KEY (story_tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_C9BA1D3EF5D1D1E3 ON tag_story (story_tag_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tag_chapter (tag_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, chapter_id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_CBB09884BAD26311 (tag_id), INDEX IDX_CBB09884579F4768 (chapter_id), PRIMARY KEY (tag_id, chapter_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE tag_chapter ADD CONSTRAINT `FK_CBB09884579F4768` FOREIGN KEY (chapter_id) REFERENCES chapter (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_chapter ADD CONSTRAINT `FK_CBB09884BAD26311` FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE media');
        $this->addSql('DROP INDEX IDX_CATEGORY_TYPE_SLUG ON category');
        $this->addSql('CREATE INDEX IDX_CATEGORY_TYPE_SLUG ON category (type, slug)');
        $this->addSql('ALTER TABLE category_movie DROP FOREIGN KEY FK_F56DBD263DC01115');
        $this->addSql('DROP INDEX IDX_F56DBD263DC01115 ON category_movie');
        $this->addSql('ALTER TABLE category_movie CHANGE movie_category_id category_id CHAR(36) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (category_id, movie_id)');
        $this->addSql('ALTER TABLE category_movie ADD CONSTRAINT `FK_F56DBD2612469DE2` FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_F56DBD2612469DE2 ON category_movie (category_id)');
        $this->addSql('ALTER TABLE category_page DROP FOREIGN KEY FK_9F91CC675FAC390');
        $this->addSql('DROP INDEX IDX_9F91CC675FAC390 ON category_page');
        $this->addSql('ALTER TABLE category_page CHANGE page_category_id category_id CHAR(36) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (category_id, page_id)');
        $this->addSql('ALTER TABLE category_page ADD CONSTRAINT `FK_9F91CC6712469DE2` FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_9F91CC6712469DE2 ON category_page (category_id)');
        $this->addSql('ALTER TABLE category_post DROP FOREIGN KEY FK_D11116CAFE0617CD');
        $this->addSql('DROP INDEX IDX_D11116CAFE0617CD ON category_post');
        $this->addSql('ALTER TABLE category_post CHANGE post_category_id category_id CHAR(36) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (category_id, post_id)');
        $this->addSql('ALTER TABLE category_post ADD CONSTRAINT `FK_D11116CA12469DE2` FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_D11116CA12469DE2 ON category_post (category_id)');
        $this->addSql('ALTER TABLE category_serie DROP FOREIGN KEY FK_4209DC7D157AAFB1');
        $this->addSql('DROP INDEX IDX_4209DC7D157AAFB1 ON category_serie');
        $this->addSql('ALTER TABLE category_serie CHANGE serie_category_id category_id CHAR(36) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (category_id, serie_id)');
        $this->addSql('ALTER TABLE category_serie ADD CONSTRAINT `FK_4209DC7D12469DE2` FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4209DC7D12469DE2 ON category_serie (category_id)');
        $this->addSql('ALTER TABLE category_story DROP FOREIGN KEY FK_3654B71E26EF55D');
        $this->addSql('DROP INDEX IDX_3654B71E26EF55D ON category_story');
        $this->addSql('ALTER TABLE category_story CHANGE story_category_id category_id CHAR(36) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (category_id, story_id)');
        $this->addSql('ALTER TABLE category_story ADD CONSTRAINT `FK_3654B7112469DE2` FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_3654B7112469DE2 ON category_story (category_id)');
        $this->addSql('ALTER TABLE paragraph DROP pdf, CHANGE save save TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE season DROP FOREIGN KEY FK_F0E45BA9691F3A10');
        $this->addSql('ALTER TABLE season ADD CONSTRAINT `FK_F0E45BA9691F3A10` FOREIGN KEY (refserie_id) REFERENCES serie (id)');
        $this->addSql('DROP INDEX IDX_TAG_TYPE_SLUG ON tag');
        $this->addSql('CREATE INDEX IDX_TAG_TYPE_SLUG ON tag (type, slug)');
        $this->addSql('ALTER TABLE tag_page DROP FOREIGN KEY FK_FA050996D504950F');
        $this->addSql('DROP INDEX IDX_FA050996D504950F ON tag_page');
        $this->addSql('ALTER TABLE tag_page CHANGE page_tag_id tag_id CHAR(36) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (tag_id, page_id)');
        $this->addSql('ALTER TABLE tag_page ADD CONSTRAINT `FK_FA050996BAD26311` FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_FA050996BAD26311 ON tag_page (tag_id)');
        $this->addSql('ALTER TABLE tag_post DROP FOREIGN KEY FK_B485D33B8AF08774');
        $this->addSql('DROP INDEX IDX_B485D33B8AF08774 ON tag_post');
        $this->addSql('ALTER TABLE tag_post CHANGE post_tag_id tag_id CHAR(36) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (tag_id, post_id)');
        $this->addSql('ALTER TABLE tag_post ADD CONSTRAINT `FK_B485D33BBAD26311` FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_B485D33BBAD26311 ON tag_post (tag_id)');
        $this->addSql('ALTER TABLE tag_story DROP FOREIGN KEY FK_C9BA1D3EF5D1D1E3');
        $this->addSql('DROP INDEX IDX_C9BA1D3EF5D1D1E3 ON tag_story');
        $this->addSql('ALTER TABLE tag_story CHANGE story_tag_id tag_id CHAR(36) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (tag_id, story_id)');
        $this->addSql('ALTER TABLE tag_story ADD CONSTRAINT `FK_C9BA1D3EBAD26311` FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_C9BA1D3EBAD26311 ON tag_story (tag_id)');
    }
}
