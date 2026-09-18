<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250205145752 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ban_ip (enable TINYINT(1) NOT NULL, id CHAR(36) NOT NULL, internet_protocol VARCHAR(255) NOT NULL, reason VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE block (content LONGTEXT DEFAULT NULL, enable TINYINT(1) DEFAULT 1 NOT NULL, id CHAR(36) NOT NULL, pages LONGTEXT DEFAULT NULL, position INT DEFAULT 1 NOT NULL, region VARCHAR(255) NOT NULL, request_path TINYINT(1) NOT NULL, roles JSON DEFAULT NULL, slug VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_831B9722989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE category (id CHAR(36) NOT NULL, slug VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, deleted_at DATETIME DEFAULT NULL, parent_id CHAR(36) DEFAULT NULL, INDEX IDX_64C19C1727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE category_movie (category_id CHAR(36) NOT NULL, movie_id CHAR(36) NOT NULL, INDEX IDX_F56DBD2612469DE2 (category_id), INDEX IDX_F56DBD268F93B6FC (movie_id), PRIMARY KEY(category_id, movie_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE category_page (category_id CHAR(36) NOT NULL, page_id CHAR(36) NOT NULL, INDEX IDX_9F91CC6712469DE2 (category_id), INDEX IDX_9F91CC67C4663E4 (page_id), PRIMARY KEY(category_id, page_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE category_post (category_id CHAR(36) NOT NULL, post_id CHAR(36) NOT NULL, INDEX IDX_D11116CA12469DE2 (category_id), INDEX IDX_D11116CA4B89032C (post_id), PRIMARY KEY(category_id, post_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE category_story (category_id CHAR(36) NOT NULL, story_id CHAR(36) NOT NULL, INDEX IDX_3654B7112469DE2 (category_id), INDEX IDX_3654B71AA5D4036 (story_id), PRIMARY KEY(category_id, story_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE chapter (enable TINYINT(1) DEFAULT 1 NOT NULL, slug VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, id CHAR(36) NOT NULL, img VARCHAR(255) DEFAULT NULL, position INT DEFAULT 1 NOT NULL, resume LONGTEXT DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, state JSON DEFAULT NULL, state_changed DATETIME DEFAULT NULL, meta_id CHAR(36) NOT NULL, refstory_id CHAR(36) NOT NULL, UNIQUE INDEX UNIQ_F981B52E989D9B62 (slug), UNIQUE INDEX UNIQ_F981B52E39FCA6F9 (meta_id), INDEX IDX_F981B52E1A01F29B (refstory_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE configuration (copyright VARCHAR(255) NOT NULL, disable_empty_agent TINYINT(1) DEFAULT 1 NOT NULL, email VARCHAR(255) NOT NULL, id CHAR(36) NOT NULL, logo VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, noreply VARCHAR(255) NOT NULL, placeholder VARCHAR(255) DEFAULT NULL, sitemap_posts TINYINT(1) DEFAULT 1 NOT NULL, sitemap_story TINYINT(1) DEFAULT 1 NOT NULL, title_format VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, user_link TINYINT(1) DEFAULT 1 NOT NULL, user_show TINYINT(1) DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE edito (enable TINYINT(1) DEFAULT 1 NOT NULL, title VARCHAR(255) NOT NULL, id CHAR(36) NOT NULL, img VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, state JSON DEFAULT NULL, state_changed DATETIME DEFAULT NULL, refuser_id CHAR(36) DEFAULT NULL, INDEX IDX_F2EC5FE02B445CEF (refuser_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE ext_translations (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(8) NOT NULL, object_class VARCHAR(191) NOT NULL, field VARCHAR(32) NOT NULL, foreign_key VARCHAR(64) NOT NULL, content LONGTEXT DEFAULT NULL, INDEX translations_lookup_idx (locale, object_class, foreign_key), INDEX general_translations_lookup_idx (object_class, foreign_key), UNIQUE INDEX lookup_unique_idx (locale, object_class, field, foreign_key), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE geo_code (accuracy INT NOT NULL, community_code VARCHAR(20) NOT NULL, community_name VARCHAR(100) NOT NULL, country_code VARCHAR(2) NOT NULL, id CHAR(36) NOT NULL, latitude VARCHAR(255) NOT NULL, longitude VARCHAR(255) NOT NULL, place_name VARCHAR(180) NOT NULL, postal_code VARCHAR(20) NOT NULL, province_code VARCHAR(20) NOT NULL, province_name VARCHAR(100) NOT NULL, state_code VARCHAR(20) NOT NULL, state_name VARCHAR(100) NOT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE http_error_logs (agent VARCHAR(255) NOT NULL, domain LONGTEXT NOT NULL, http_code VARCHAR(255) NOT NULL, id CHAR(36) NOT NULL, internet_protocol VARCHAR(255) NOT NULL, referer LONGTEXT DEFAULT NULL, request_data JSON NOT NULL, request_method VARCHAR(255) DEFAULT NULL, url LONGTEXT NOT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, refuser_id CHAR(36) DEFAULT NULL, INDEX IDX_4B946BCF2B445CEF (refuser_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE link (blank TINYINT(1) NOT NULL, classes VARCHAR(255) DEFAULT NULL, id CHAR(36) NOT NULL, title VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, block_id CHAR(36) DEFAULT NULL, INDEX IDX_36AC99F1E9ED820C (block_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE memo (enable TINYINT(1) DEFAULT 1 NOT NULL, title VARCHAR(255) NOT NULL, id CHAR(36) NOT NULL, img VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, state JSON DEFAULT NULL, state_changed DATETIME DEFAULT NULL, refuser_id CHAR(36) DEFAULT NULL, INDEX IDX_AB4A902A2B445CEF (refuser_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE meta (description VARCHAR(255) DEFAULT NULL, id CHAR(36) NOT NULL, keywords VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE movie (color VARCHAR(255) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, duration INT DEFAULT NULL, enable TINYINT(1) DEFAULT 1 NOT NULL, evaluation DOUBLE PRECISION DEFAULT NULL, id CHAR(36) NOT NULL, imdb VARCHAR(255) NOT NULL, img VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, trailer LONGTEXT DEFAULT NULL, votes INT DEFAULT NULL, year INT DEFAULT NULL, description LONGTEXT DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE page (enable TINYINT(1) DEFAULT 1 NOT NULL, slug VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, id CHAR(36) NOT NULL, img VARCHAR(255) DEFAULT NULL, resume LONGTEXT DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, state JSON DEFAULT NULL, state_changed DATETIME DEFAULT NULL, meta_id CHAR(36) NOT NULL, page_id CHAR(36) DEFAULT NULL, refuser_id CHAR(36) DEFAULT NULL, UNIQUE INDEX UNIQ_140AB620989D9B62 (slug), UNIQUE INDEX UNIQ_140AB62039FCA6F9 (meta_id), INDEX IDX_140AB620C4663E4 (page_id), INDEX IDX_140AB6202B445CEF (refuser_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE paragraph (content LONGTEXT DEFAULT NULL, enable TINYINT(1) DEFAULT 1 NOT NULL, fond VARCHAR(255) DEFAULT NULL, form VARCHAR(255) DEFAULT NULL, id CHAR(36) NOT NULL, img VARCHAR(255) DEFAULT NULL, nbr INT DEFAULT NULL, position INT NOT NULL, save TINYINT(1) DEFAULT 1 NOT NULL, title VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, url VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, block_id CHAR(36) DEFAULT NULL, chapter_id CHAR(36) DEFAULT NULL, edito_id CHAR(36) DEFAULT NULL, memo_id CHAR(36) DEFAULT NULL, page_id CHAR(36) DEFAULT NULL, post_id CHAR(36) DEFAULT NULL, story_id CHAR(36) DEFAULT NULL, INDEX IDX_7DD39862E9ED820C (block_id), INDEX IDX_7DD39862579F4768 (chapter_id), INDEX IDX_7DD398625B3CFAAA (edito_id), INDEX IDX_7DD39862B4D32439 (memo_id), INDEX IDX_7DD39862C4663E4 (page_id), INDEX IDX_7DD398624B89032C (post_id), INDEX IDX_7DD39862AA5D4036 (story_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE post (enable TINYINT(1) DEFAULT 1 NOT NULL, slug VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, id CHAR(36) NOT NULL, img VARCHAR(255) DEFAULT NULL, resume LONGTEXT DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, state JSON DEFAULT NULL, state_changed DATETIME DEFAULT NULL, meta_id CHAR(36) NOT NULL, refuser_id CHAR(36) DEFAULT NULL, UNIQUE INDEX UNIQ_5A8A6C8D989D9B62 (slug), UNIQUE INDEX UNIQ_5A8A6C8D39FCA6F9 (meta_id), INDEX IDX_5A8A6C8D2B445CEF (refuser_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE redirection (action_code INT NOT NULL, action_type LONGTEXT NOT NULL, data JSON NOT NULL, destination LONGTEXT NOT NULL, enable TINYINT(1) NOT NULL, id CHAR(36) NOT NULL, last_count INT NOT NULL, position INT NOT NULL, regex TINYINT(1) NOT NULL, source LONGTEXT NOT NULL, title VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE star (description LONGTEXT DEFAULT NULL, enable TINYINT(1) DEFAULT 1 NOT NULL, forks INT NOT NULL, id CHAR(36) NOT NULL, language VARCHAR(255) DEFAULT NULL, license VARCHAR(255) DEFAULT NULL, repository VARCHAR(255) NOT NULL, stargazers INT NOT NULL, title VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, watchers INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE story (enable TINYINT(1) DEFAULT 1 NOT NULL, slug VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, id CHAR(36) NOT NULL, img VARCHAR(255) DEFAULT NULL, pdf VARCHAR(255) DEFAULT NULL, resume LONGTEXT DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, state JSON DEFAULT NULL, state_changed DATETIME DEFAULT NULL, meta_id CHAR(36) NOT NULL, refuser_id CHAR(36) DEFAULT NULL, UNIQUE INDEX UNIQ_EB560438989D9B62 (slug), UNIQUE INDEX UNIQ_EB56043839FCA6F9 (meta_id), INDEX IDX_EB5604382B445CEF (refuser_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE submission (data JSON NOT NULL, id CHAR(36) NOT NULL, type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE tag (id CHAR(36) NOT NULL, slug VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE tag_chapter (tag_id CHAR(36) NOT NULL, chapter_id CHAR(36) NOT NULL, INDEX IDX_CBB09884BAD26311 (tag_id), INDEX IDX_CBB09884579F4768 (chapter_id), PRIMARY KEY(tag_id, chapter_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE tag_page (tag_id CHAR(36) NOT NULL, page_id CHAR(36) NOT NULL, INDEX IDX_FA050996BAD26311 (tag_id), INDEX IDX_FA050996C4663E4 (page_id), PRIMARY KEY(tag_id, page_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE tag_post (tag_id CHAR(36) NOT NULL, post_id CHAR(36) NOT NULL, INDEX IDX_B485D33BBAD26311 (tag_id), INDEX IDX_B485D33B4B89032C (post_id), PRIMARY KEY(tag_id, post_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE tag_story (tag_id CHAR(36) NOT NULL, story_id CHAR(36) NOT NULL, INDEX IDX_C9BA1D3EBAD26311 (tag_id), INDEX IDX_C9BA1D3EAA5D4036 (story_id), PRIMARY KEY(tag_id, story_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE template (code VARCHAR(255) NOT NULL, html LONGTEXT DEFAULT NULL, id CHAR(36) NOT NULL, text LONGTEXT DEFAULT NULL, title VARCHAR(255) NOT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user (avatar VARCHAR(255) DEFAULT NULL, email VARCHAR(180) NOT NULL, enable TINYINT(1) DEFAULT 1 NOT NULL, id CHAR(36) NOT NULL, language VARCHAR(2) DEFAULT \'fr\' NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL, username VARCHAR(255) NOT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, state JSON DEFAULT NULL, state_changed DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE rememberme_token (series VARCHAR(88) NOT NULL, value VARCHAR(88) NOT NULL, lastUsed DATETIME NOT NULL, class VARCHAR(100) NOT NULL, username VARCHAR(200) NOT NULL, PRIMARY KEY(series)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1727ACA70 FOREIGN KEY (parent_id) REFERENCES category (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE category_movie ADD CONSTRAINT FK_F56DBD2612469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_movie ADD CONSTRAINT FK_F56DBD268F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_page ADD CONSTRAINT FK_9F91CC6712469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_page ADD CONSTRAINT FK_9F91CC67C4663E4 FOREIGN KEY (page_id) REFERENCES page (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_post ADD CONSTRAINT FK_D11116CA12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_post ADD CONSTRAINT FK_D11116CA4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_story ADD CONSTRAINT FK_3654B7112469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_story ADD CONSTRAINT FK_3654B71AA5D4036 FOREIGN KEY (story_id) REFERENCES story (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chapter ADD CONSTRAINT FK_F981B52E39FCA6F9 FOREIGN KEY (meta_id) REFERENCES meta (id)');
        $this->addSql('ALTER TABLE chapter ADD CONSTRAINT FK_F981B52E1A01F29B FOREIGN KEY (refstory_id) REFERENCES story (id)');
        $this->addSql('ALTER TABLE edito ADD CONSTRAINT FK_F2EC5FE02B445CEF FOREIGN KEY (refuser_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE http_error_logs ADD CONSTRAINT FK_4B946BCF2B445CEF FOREIGN KEY (refuser_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE link ADD CONSTRAINT FK_36AC99F1E9ED820C FOREIGN KEY (block_id) REFERENCES block (id)');
        $this->addSql('ALTER TABLE memo ADD CONSTRAINT FK_AB4A902A2B445CEF FOREIGN KEY (refuser_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB62039FCA6F9 FOREIGN KEY (meta_id) REFERENCES meta (id)');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB620C4663E4 FOREIGN KEY (page_id) REFERENCES page (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB6202B445CEF FOREIGN KEY (refuser_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD39862E9ED820C FOREIGN KEY (block_id) REFERENCES block (id)');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD39862579F4768 FOREIGN KEY (chapter_id) REFERENCES chapter (id)');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD398625B3CFAAA FOREIGN KEY (edito_id) REFERENCES edito (id)');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD39862B4D32439 FOREIGN KEY (memo_id) REFERENCES memo (id)');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD39862C4663E4 FOREIGN KEY (page_id) REFERENCES page (id)');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD398624B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD39862AA5D4036 FOREIGN KEY (story_id) REFERENCES story (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D39FCA6F9 FOREIGN KEY (meta_id) REFERENCES meta (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D2B445CEF FOREIGN KEY (refuser_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE story ADD CONSTRAINT FK_EB56043839FCA6F9 FOREIGN KEY (meta_id) REFERENCES meta (id)');
        $this->addSql('ALTER TABLE story ADD CONSTRAINT FK_EB5604382B445CEF FOREIGN KEY (refuser_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tag_chapter ADD CONSTRAINT FK_CBB09884BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_chapter ADD CONSTRAINT FK_CBB09884579F4768 FOREIGN KEY (chapter_id) REFERENCES chapter (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_page ADD CONSTRAINT FK_FA050996BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_page ADD CONSTRAINT FK_FA050996C4663E4 FOREIGN KEY (page_id) REFERENCES page (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_post ADD CONSTRAINT FK_B485D33BBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_post ADD CONSTRAINT FK_B485D33B4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_story ADD CONSTRAINT FK_C9BA1D3EBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_story ADD CONSTRAINT FK_C9BA1D3EAA5D4036 FOREIGN KEY (story_id) REFERENCES story (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1727ACA70');
        $this->addSql('ALTER TABLE category_movie DROP FOREIGN KEY FK_F56DBD2612469DE2');
        $this->addSql('ALTER TABLE category_movie DROP FOREIGN KEY FK_F56DBD268F93B6FC');
        $this->addSql('ALTER TABLE category_page DROP FOREIGN KEY FK_9F91CC6712469DE2');
        $this->addSql('ALTER TABLE category_page DROP FOREIGN KEY FK_9F91CC67C4663E4');
        $this->addSql('ALTER TABLE category_post DROP FOREIGN KEY FK_D11116CA12469DE2');
        $this->addSql('ALTER TABLE category_post DROP FOREIGN KEY FK_D11116CA4B89032C');
        $this->addSql('ALTER TABLE category_story DROP FOREIGN KEY FK_3654B7112469DE2');
        $this->addSql('ALTER TABLE category_story DROP FOREIGN KEY FK_3654B71AA5D4036');
        $this->addSql('ALTER TABLE chapter DROP FOREIGN KEY FK_F981B52E39FCA6F9');
        $this->addSql('ALTER TABLE chapter DROP FOREIGN KEY FK_F981B52E1A01F29B');
        $this->addSql('ALTER TABLE edito DROP FOREIGN KEY FK_F2EC5FE02B445CEF');
        $this->addSql('ALTER TABLE http_error_logs DROP FOREIGN KEY FK_4B946BCF2B445CEF');
        $this->addSql('ALTER TABLE link DROP FOREIGN KEY FK_36AC99F1E9ED820C');
        $this->addSql('ALTER TABLE memo DROP FOREIGN KEY FK_AB4A902A2B445CEF');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB62039FCA6F9');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB620C4663E4');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB6202B445CEF');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD39862E9ED820C');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD39862579F4768');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD398625B3CFAAA');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD39862B4D32439');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD39862C4663E4');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD398624B89032C');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD39862AA5D4036');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D39FCA6F9');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D2B445CEF');
        $this->addSql('ALTER TABLE story DROP FOREIGN KEY FK_EB56043839FCA6F9');
        $this->addSql('ALTER TABLE story DROP FOREIGN KEY FK_EB5604382B445CEF');
        $this->addSql('ALTER TABLE tag_chapter DROP FOREIGN KEY FK_CBB09884BAD26311');
        $this->addSql('ALTER TABLE tag_chapter DROP FOREIGN KEY FK_CBB09884579F4768');
        $this->addSql('ALTER TABLE tag_page DROP FOREIGN KEY FK_FA050996BAD26311');
        $this->addSql('ALTER TABLE tag_page DROP FOREIGN KEY FK_FA050996C4663E4');
        $this->addSql('ALTER TABLE tag_post DROP FOREIGN KEY FK_B485D33BBAD26311');
        $this->addSql('ALTER TABLE tag_post DROP FOREIGN KEY FK_B485D33B4B89032C');
        $this->addSql('ALTER TABLE tag_story DROP FOREIGN KEY FK_C9BA1D3EBAD26311');
        $this->addSql('ALTER TABLE tag_story DROP FOREIGN KEY FK_C9BA1D3EAA5D4036');
        $this->addSql('DROP TABLE ban_ip');
        $this->addSql('DROP TABLE block');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE category_movie');
        $this->addSql('DROP TABLE category_page');
        $this->addSql('DROP TABLE category_post');
        $this->addSql('DROP TABLE category_story');
        $this->addSql('DROP TABLE chapter');
        $this->addSql('DROP TABLE configuration');
        $this->addSql('DROP TABLE edito');
        $this->addSql('DROP TABLE ext_translations');
        $this->addSql('DROP TABLE geo_code');
        $this->addSql('DROP TABLE http_error_logs');
        $this->addSql('DROP TABLE link');
        $this->addSql('DROP TABLE memo');
        $this->addSql('DROP TABLE meta');
        $this->addSql('DROP TABLE movie');
        $this->addSql('DROP TABLE page');
        $this->addSql('DROP TABLE paragraph');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE redirection');
        $this->addSql('DROP TABLE star');
        $this->addSql('DROP TABLE story');
        $this->addSql('DROP TABLE submission');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE tag_chapter');
        $this->addSql('DROP TABLE tag_page');
        $this->addSql('DROP TABLE tag_post');
        $this->addSql('DROP TABLE tag_story');
        $this->addSql('DROP TABLE template');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE rememberme_token');
    }
}
