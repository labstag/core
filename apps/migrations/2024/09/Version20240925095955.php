<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240925095955 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category_history (category_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', history_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_E062303512469DE2 (category_id), INDEX IDX_E06230351E058452 (history_id), PRIMARY KEY(category_id, history_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category_page (category_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', page_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_9F91CC6712469DE2 (category_id), INDEX IDX_9F91CC67C4663E4 (page_id), PRIMARY KEY(category_id, page_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category_post (category_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', post_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_D11116CA12469DE2 (category_id), INDEX IDX_D11116CA4B89032C (post_id), PRIMARY KEY(category_id, post_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_post (tag_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', post_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_B485D33BBAD26311 (tag_id), INDEX IDX_B485D33B4B89032C (post_id), PRIMARY KEY(tag_id, post_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_page (tag_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', page_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_FA050996BAD26311 (tag_id), INDEX IDX_FA050996C4663E4 (page_id), PRIMARY KEY(tag_id, page_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_history (tag_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', history_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_158B5DE1BAD26311 (tag_id), INDEX IDX_158B5DE11E058452 (history_id), PRIMARY KEY(tag_id, history_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_edito (tag_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', edito_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_D00046E6BAD26311 (tag_id), INDEX IDX_D00046E65B3CFAAA (edito_id), PRIMARY KEY(tag_id, edito_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_memo (tag_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', memo_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_45452F9CBAD26311 (tag_id), INDEX IDX_45452F9CB4D32439 (memo_id), PRIMARY KEY(tag_id, memo_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_chapter (tag_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', chapter_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_CBB09884BAD26311 (tag_id), INDEX IDX_CBB09884579F4768 (chapter_id), PRIMARY KEY(tag_id, chapter_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE category_history ADD CONSTRAINT FK_E062303512469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_history ADD CONSTRAINT FK_E06230351E058452 FOREIGN KEY (history_id) REFERENCES history (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_page ADD CONSTRAINT FK_9F91CC6712469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_page ADD CONSTRAINT FK_9F91CC67C4663E4 FOREIGN KEY (page_id) REFERENCES page (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_post ADD CONSTRAINT FK_D11116CA12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_post ADD CONSTRAINT FK_D11116CA4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_post ADD CONSTRAINT FK_B485D33BBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_post ADD CONSTRAINT FK_B485D33B4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_page ADD CONSTRAINT FK_FA050996BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_page ADD CONSTRAINT FK_FA050996C4663E4 FOREIGN KEY (page_id) REFERENCES page (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_history ADD CONSTRAINT FK_158B5DE1BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_history ADD CONSTRAINT FK_158B5DE11E058452 FOREIGN KEY (history_id) REFERENCES history (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_edito ADD CONSTRAINT FK_D00046E6BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_edito ADD CONSTRAINT FK_D00046E65B3CFAAA FOREIGN KEY (edito_id) REFERENCES edito (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_memo ADD CONSTRAINT FK_45452F9CBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_memo ADD CONSTRAINT FK_45452F9CB4D32439 FOREIGN KEY (memo_id) REFERENCES memo (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_chapter ADD CONSTRAINT FK_CBB09884BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_chapter ADD CONSTRAINT FK_CBB09884579F4768 FOREIGN KEY (chapter_id) REFERENCES chapter (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE edito ADD refuser_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE edito ADD CONSTRAINT FK_F2EC5FE02B445CEF FOREIGN KEY (refuser_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_F2EC5FE02B445CEF ON edito (refuser_id)');
        $this->addSql('ALTER TABLE file ADD name VARCHAR(255) NOT NULL, ADD original_name VARCHAR(255) NOT NULL, ADD mime_type VARCHAR(255) NOT NULL, ADD size INT NOT NULL, ADD dimensions LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE history ADD refuser_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE history ADD CONSTRAINT FK_27BA704B2B445CEF FOREIGN KEY (refuser_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_27BA704B2B445CEF ON history (refuser_id)');
        $this->addSql('ALTER TABLE memo ADD refuser_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE memo ADD CONSTRAINT FK_AB4A902A2B445CEF FOREIGN KEY (refuser_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_AB4A902A2B445CEF ON memo (refuser_id)');
        $this->addSql('ALTER TABLE page ADD refuser_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB6202B445CEF FOREIGN KEY (refuser_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_140AB6202B445CEF ON page (refuser_id)');
        $this->addSql('ALTER TABLE post ADD refuser_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D2B445CEF FOREIGN KEY (refuser_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D2B445CEF ON post (refuser_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category_history DROP FOREIGN KEY FK_E062303512469DE2');
        $this->addSql('ALTER TABLE category_history DROP FOREIGN KEY FK_E06230351E058452');
        $this->addSql('ALTER TABLE category_page DROP FOREIGN KEY FK_9F91CC6712469DE2');
        $this->addSql('ALTER TABLE category_page DROP FOREIGN KEY FK_9F91CC67C4663E4');
        $this->addSql('ALTER TABLE category_post DROP FOREIGN KEY FK_D11116CA12469DE2');
        $this->addSql('ALTER TABLE category_post DROP FOREIGN KEY FK_D11116CA4B89032C');
        $this->addSql('ALTER TABLE tag_post DROP FOREIGN KEY FK_B485D33BBAD26311');
        $this->addSql('ALTER TABLE tag_post DROP FOREIGN KEY FK_B485D33B4B89032C');
        $this->addSql('ALTER TABLE tag_page DROP FOREIGN KEY FK_FA050996BAD26311');
        $this->addSql('ALTER TABLE tag_page DROP FOREIGN KEY FK_FA050996C4663E4');
        $this->addSql('ALTER TABLE tag_history DROP FOREIGN KEY FK_158B5DE1BAD26311');
        $this->addSql('ALTER TABLE tag_history DROP FOREIGN KEY FK_158B5DE11E058452');
        $this->addSql('ALTER TABLE tag_edito DROP FOREIGN KEY FK_D00046E6BAD26311');
        $this->addSql('ALTER TABLE tag_edito DROP FOREIGN KEY FK_D00046E65B3CFAAA');
        $this->addSql('ALTER TABLE tag_memo DROP FOREIGN KEY FK_45452F9CBAD26311');
        $this->addSql('ALTER TABLE tag_memo DROP FOREIGN KEY FK_45452F9CB4D32439');
        $this->addSql('ALTER TABLE tag_chapter DROP FOREIGN KEY FK_CBB09884BAD26311');
        $this->addSql('ALTER TABLE tag_chapter DROP FOREIGN KEY FK_CBB09884579F4768');
        $this->addSql('DROP TABLE category_history');
        $this->addSql('DROP TABLE category_page');
        $this->addSql('DROP TABLE category_post');
        $this->addSql('DROP TABLE tag_post');
        $this->addSql('DROP TABLE tag_page');
        $this->addSql('DROP TABLE tag_history');
        $this->addSql('DROP TABLE tag_edito');
        $this->addSql('DROP TABLE tag_memo');
        $this->addSql('DROP TABLE tag_chapter');
        $this->addSql('ALTER TABLE edito DROP FOREIGN KEY FK_F2EC5FE02B445CEF');
        $this->addSql('DROP INDEX IDX_F2EC5FE02B445CEF ON edito');
        $this->addSql('ALTER TABLE edito DROP refuser_id');
        $this->addSql('ALTER TABLE memo DROP FOREIGN KEY FK_AB4A902A2B445CEF');
        $this->addSql('DROP INDEX IDX_AB4A902A2B445CEF ON memo');
        $this->addSql('ALTER TABLE memo DROP refuser_id');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D2B445CEF');
        $this->addSql('DROP INDEX IDX_5A8A6C8D2B445CEF ON post');
        $this->addSql('ALTER TABLE post DROP refuser_id');
        $this->addSql('ALTER TABLE history DROP FOREIGN KEY FK_27BA704B2B445CEF');
        $this->addSql('DROP INDEX IDX_27BA704B2B445CEF ON history');
        $this->addSql('ALTER TABLE history DROP refuser_id');
        $this->addSql('ALTER TABLE file DROP name, DROP original_name, DROP mime_type, DROP size, DROP dimensions');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB6202B445CEF');
        $this->addSql('DROP INDEX IDX_140AB6202B445CEF ON page');
        $this->addSql('ALTER TABLE page DROP refuser_id');
    }
}
