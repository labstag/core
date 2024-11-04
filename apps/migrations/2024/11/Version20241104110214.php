<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241104110214 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE block (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', enable TINYINT(1) DEFAULT 1 NOT NULL, position INT DEFAULT 1 NOT NULL, region VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, roles LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', pages LONGTEXT DEFAULT NULL, content VARCHAR(255) DEFAULT NULL, request_path TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_831B9722989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', parent_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', slug VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_64C19C1727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category_history (category_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', history_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_E062303512469DE2 (category_id), INDEX IDX_E06230351E058452 (history_id), PRIMARY KEY(category_id, history_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category_page (category_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', page_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_9F91CC6712469DE2 (category_id), INDEX IDX_9F91CC67C4663E4 (page_id), PRIMARY KEY(category_id, page_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category_post (category_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', post_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_D11116CA12469DE2 (category_id), INDEX IDX_D11116CA4B89032C (post_id), PRIMARY KEY(category_id, post_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chapter (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', meta_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', refhistory_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', enable TINYINT(1) DEFAULT 1 NOT NULL, slug VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, img VARCHAR(255) DEFAULT NULL, position INT DEFAULT 1 NOT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, current_place JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', UNIQUE INDEX UNIQ_F981B52E989D9B62 (slug), UNIQUE INDEX UNIQ_F981B52E39FCA6F9 (meta_id), INDEX IDX_F981B52E20C3240A (refhistory_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE configuration (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(255) NOT NULL, value JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE edito (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', refuser_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', enable TINYINT(1) DEFAULT 1 NOT NULL, title VARCHAR(255) NOT NULL, img VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, current_place JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_F2EC5FE02B445CEF (refuser_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ext_log_entries (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(8) NOT NULL, logged_at DATETIME NOT NULL, object_id VARCHAR(64) DEFAULT NULL, object_class VARCHAR(191) NOT NULL, version INT NOT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', username VARCHAR(191) DEFAULT NULL, INDEX log_class_lookup_idx (object_class), INDEX log_date_lookup_idx (logged_at), INDEX log_user_lookup_idx (username), INDEX log_version_lookup_idx (object_id, object_class, version), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE ext_translations (id INT AUTO_INCREMENT NOT NULL, locale VARCHAR(8) NOT NULL, object_class VARCHAR(191) NOT NULL, field VARCHAR(32) NOT NULL, foreign_key VARCHAR(64) NOT NULL, content LONGTEXT DEFAULT NULL, INDEX translations_lookup_idx (locale, object_class, foreign_key), INDEX general_translations_lookup_idx (object_class, foreign_key), UNIQUE INDEX lookup_unique_idx (locale, object_class, field, foreign_key), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE history (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', meta_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', refuser_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', enable TINYINT(1) DEFAULT 1 NOT NULL, slug VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, img VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, current_place JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', UNIQUE INDEX UNIQ_27BA704B989D9B62 (slug), UNIQUE INDEX UNIQ_27BA704B39FCA6F9 (meta_id), INDEX IDX_27BA704B2B445CEF (refuser_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE memo (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', refuser_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', enable TINYINT(1) DEFAULT 1 NOT NULL, title VARCHAR(255) NOT NULL, img VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, current_place JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_AB4A902A2B445CEF (refuser_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meta (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', description VARCHAR(255) DEFAULT NULL, keywords VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE page (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', meta_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', page_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', refuser_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', enable TINYINT(1) DEFAULT 1 NOT NULL, slug VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, img VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, current_place JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', UNIQUE INDEX UNIQ_140AB620989D9B62 (slug), UNIQUE INDEX UNIQ_140AB62039FCA6F9 (meta_id), INDEX IDX_140AB620C4663E4 (page_id), INDEX IDX_140AB6202B445CEF (refuser_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE paragraph (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', block_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', chapter_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', edito_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', history_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', memo_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', page_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', post_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', content LONGTEXT DEFAULT NULL, enable TINYINT(1) DEFAULT 1 NOT NULL, fond VARCHAR(255) DEFAULT NULL, img VARCHAR(255) DEFAULT NULL, position INT NOT NULL, title VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_7DD39862E9ED820C (block_id), INDEX IDX_7DD39862579F4768 (chapter_id), INDEX IDX_7DD398625B3CFAAA (edito_id), INDEX IDX_7DD398621E058452 (history_id), INDEX IDX_7DD39862B4D32439 (memo_id), INDEX IDX_7DD39862C4663E4 (page_id), INDEX IDX_7DD398624B89032C (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', meta_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', refuser_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', enable TINYINT(1) DEFAULT 1 NOT NULL, slug VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, img VARCHAR(255) DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, current_place JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', UNIQUE INDEX UNIQ_5A8A6C8D989D9B62 (slug), UNIQUE INDEX UNIQ_5A8A6C8D39FCA6F9 (meta_id), INDEX IDX_5A8A6C8D2B445CEF (refuser_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', slug VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_chapter (tag_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', chapter_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_CBB09884BAD26311 (tag_id), INDEX IDX_CBB09884579F4768 (chapter_id), PRIMARY KEY(tag_id, chapter_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_edito (tag_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', edito_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_D00046E6BAD26311 (tag_id), INDEX IDX_D00046E65B3CFAAA (edito_id), PRIMARY KEY(tag_id, edito_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_history (tag_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', history_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_158B5DE1BAD26311 (tag_id), INDEX IDX_158B5DE11E058452 (history_id), PRIMARY KEY(tag_id, history_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_memo (tag_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', memo_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_45452F9CBAD26311 (tag_id), INDEX IDX_45452F9CB4D32439 (memo_id), PRIMARY KEY(tag_id, memo_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_page (tag_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', page_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_FA050996BAD26311 (tag_id), INDEX IDX_FA050996C4663E4 (page_id), PRIMARY KEY(tag_id, page_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_post (tag_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', post_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_B485D33BBAD26311 (tag_id), INDEX IDX_B485D33B4B89032C (post_id), PRIMARY KEY(tag_id, post_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', avatar VARCHAR(255) DEFAULT NULL, email VARCHAR(180) NOT NULL, enable TINYINT(1) DEFAULT 1 NOT NULL, language VARCHAR(2) DEFAULT \'fr\' NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', username VARCHAR(255) NOT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, current_place JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rememberme_token (series VARCHAR(88) NOT NULL, value VARCHAR(88) NOT NULL, lastUsed DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', class VARCHAR(100) NOT NULL, username VARCHAR(200) NOT NULL, PRIMARY KEY(series)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1727ACA70 FOREIGN KEY (parent_id) REFERENCES category (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE category_history ADD CONSTRAINT FK_E062303512469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_history ADD CONSTRAINT FK_E06230351E058452 FOREIGN KEY (history_id) REFERENCES history (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_page ADD CONSTRAINT FK_9F91CC6712469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_page ADD CONSTRAINT FK_9F91CC67C4663E4 FOREIGN KEY (page_id) REFERENCES page (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_post ADD CONSTRAINT FK_D11116CA12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_post ADD CONSTRAINT FK_D11116CA4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chapter ADD CONSTRAINT FK_F981B52E39FCA6F9 FOREIGN KEY (meta_id) REFERENCES meta (id)');
        $this->addSql('ALTER TABLE chapter ADD CONSTRAINT FK_F981B52E20C3240A FOREIGN KEY (refhistory_id) REFERENCES history (id)');
        $this->addSql('ALTER TABLE edito ADD CONSTRAINT FK_F2EC5FE02B445CEF FOREIGN KEY (refuser_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE history ADD CONSTRAINT FK_27BA704B39FCA6F9 FOREIGN KEY (meta_id) REFERENCES meta (id)');
        $this->addSql('ALTER TABLE history ADD CONSTRAINT FK_27BA704B2B445CEF FOREIGN KEY (refuser_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE memo ADD CONSTRAINT FK_AB4A902A2B445CEF FOREIGN KEY (refuser_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB62039FCA6F9 FOREIGN KEY (meta_id) REFERENCES meta (id)');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB620C4663E4 FOREIGN KEY (page_id) REFERENCES page (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB6202B445CEF FOREIGN KEY (refuser_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD39862E9ED820C FOREIGN KEY (block_id) REFERENCES block (id)');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD39862579F4768 FOREIGN KEY (chapter_id) REFERENCES chapter (id)');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD398625B3CFAAA FOREIGN KEY (edito_id) REFERENCES edito (id)');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD398621E058452 FOREIGN KEY (history_id) REFERENCES history (id)');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD39862B4D32439 FOREIGN KEY (memo_id) REFERENCES memo (id)');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD39862C4663E4 FOREIGN KEY (page_id) REFERENCES page (id)');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD398624B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D39FCA6F9 FOREIGN KEY (meta_id) REFERENCES meta (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D2B445CEF FOREIGN KEY (refuser_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tag_chapter ADD CONSTRAINT FK_CBB09884BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_chapter ADD CONSTRAINT FK_CBB09884579F4768 FOREIGN KEY (chapter_id) REFERENCES chapter (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_edito ADD CONSTRAINT FK_D00046E6BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_edito ADD CONSTRAINT FK_D00046E65B3CFAAA FOREIGN KEY (edito_id) REFERENCES edito (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_history ADD CONSTRAINT FK_158B5DE1BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_history ADD CONSTRAINT FK_158B5DE11E058452 FOREIGN KEY (history_id) REFERENCES history (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_memo ADD CONSTRAINT FK_45452F9CBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_memo ADD CONSTRAINT FK_45452F9CB4D32439 FOREIGN KEY (memo_id) REFERENCES memo (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_page ADD CONSTRAINT FK_FA050996BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_page ADD CONSTRAINT FK_FA050996C4663E4 FOREIGN KEY (page_id) REFERENCES page (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_post ADD CONSTRAINT FK_B485D33BBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_post ADD CONSTRAINT FK_B485D33B4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1727ACA70');
        $this->addSql('ALTER TABLE category_history DROP FOREIGN KEY FK_E062303512469DE2');
        $this->addSql('ALTER TABLE category_history DROP FOREIGN KEY FK_E06230351E058452');
        $this->addSql('ALTER TABLE category_page DROP FOREIGN KEY FK_9F91CC6712469DE2');
        $this->addSql('ALTER TABLE category_page DROP FOREIGN KEY FK_9F91CC67C4663E4');
        $this->addSql('ALTER TABLE category_post DROP FOREIGN KEY FK_D11116CA12469DE2');
        $this->addSql('ALTER TABLE category_post DROP FOREIGN KEY FK_D11116CA4B89032C');
        $this->addSql('ALTER TABLE chapter DROP FOREIGN KEY FK_F981B52E39FCA6F9');
        $this->addSql('ALTER TABLE chapter DROP FOREIGN KEY FK_F981B52E20C3240A');
        $this->addSql('ALTER TABLE edito DROP FOREIGN KEY FK_F2EC5FE02B445CEF');
        $this->addSql('ALTER TABLE history DROP FOREIGN KEY FK_27BA704B39FCA6F9');
        $this->addSql('ALTER TABLE history DROP FOREIGN KEY FK_27BA704B2B445CEF');
        $this->addSql('ALTER TABLE memo DROP FOREIGN KEY FK_AB4A902A2B445CEF');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB62039FCA6F9');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB620C4663E4');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB6202B445CEF');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD39862E9ED820C');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD39862579F4768');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD398625B3CFAAA');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD398621E058452');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD39862B4D32439');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD39862C4663E4');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD398624B89032C');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D39FCA6F9');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D2B445CEF');
        $this->addSql('ALTER TABLE tag_chapter DROP FOREIGN KEY FK_CBB09884BAD26311');
        $this->addSql('ALTER TABLE tag_chapter DROP FOREIGN KEY FK_CBB09884579F4768');
        $this->addSql('ALTER TABLE tag_edito DROP FOREIGN KEY FK_D00046E6BAD26311');
        $this->addSql('ALTER TABLE tag_edito DROP FOREIGN KEY FK_D00046E65B3CFAAA');
        $this->addSql('ALTER TABLE tag_history DROP FOREIGN KEY FK_158B5DE1BAD26311');
        $this->addSql('ALTER TABLE tag_history DROP FOREIGN KEY FK_158B5DE11E058452');
        $this->addSql('ALTER TABLE tag_memo DROP FOREIGN KEY FK_45452F9CBAD26311');
        $this->addSql('ALTER TABLE tag_memo DROP FOREIGN KEY FK_45452F9CB4D32439');
        $this->addSql('ALTER TABLE tag_page DROP FOREIGN KEY FK_FA050996BAD26311');
        $this->addSql('ALTER TABLE tag_page DROP FOREIGN KEY FK_FA050996C4663E4');
        $this->addSql('ALTER TABLE tag_post DROP FOREIGN KEY FK_B485D33BBAD26311');
        $this->addSql('ALTER TABLE tag_post DROP FOREIGN KEY FK_B485D33B4B89032C');
        $this->addSql('DROP TABLE block');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE category_history');
        $this->addSql('DROP TABLE category_page');
        $this->addSql('DROP TABLE category_post');
        $this->addSql('DROP TABLE chapter');
        $this->addSql('DROP TABLE configuration');
        $this->addSql('DROP TABLE edito');
        $this->addSql('DROP TABLE ext_log_entries');
        $this->addSql('DROP TABLE ext_translations');
        $this->addSql('DROP TABLE history');
        $this->addSql('DROP TABLE memo');
        $this->addSql('DROP TABLE meta');
        $this->addSql('DROP TABLE page');
        $this->addSql('DROP TABLE paragraph');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE tag_chapter');
        $this->addSql('DROP TABLE tag_edito');
        $this->addSql('DROP TABLE tag_history');
        $this->addSql('DROP TABLE tag_memo');
        $this->addSql('DROP TABLE tag_page');
        $this->addSql('DROP TABLE tag_post');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE rememberme_token');
    }
}
