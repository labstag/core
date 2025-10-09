<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251009100614 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE saga (description LONGTEXT DEFAULT NULL, id CHAR(36) NOT NULL, img VARCHAR(255) DEFAULT NULL, slug VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, tmdb VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_SAGA_SLUG (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE tag_movie (tag_id CHAR(36) NOT NULL, movie_id CHAR(36) NOT NULL, INDEX IDX_3FB2EB69BAD26311 (tag_id), INDEX IDX_3FB2EB698F93B6FC (movie_id), PRIMARY KEY (tag_id, movie_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE tag_movie ADD CONSTRAINT FK_3FB2EB69BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_movie ADD CONSTRAINT FK_3FB2EB698F93B6FC FOREIGN KEY (movie_id) REFERENCES movie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE block ADD classes VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_BLOCK_SLUG ON block (slug)');
        $this->addSql('CREATE INDEX IDX_CATEGORY_TYPE_SLUG ON category (type, slug)');
        $this->addSql('CREATE INDEX IDX_CHAPTER_SLUG ON chapter (slug)');
        $this->addSql('ALTER TABLE configuration ADD tab_icon_src VARCHAR(255) DEFAULT NULL, ADD tac_accept_all_cta TINYINT(1) DEFAULT 1 NOT NULL, ADD tac_adblocker TINYINT(1) DEFAULT 0 NOT NULL, ADD tac_always_need_consent TINYINT(1) DEFAULT 0 NOT NULL, ADD tac_body_position VARCHAR(255) DEFAULT \'top\', ADD tac_close_popup TINYINT(1) DEFAULT 1 NOT NULL, ADD tac_cookie_domain VARCHAR(255) DEFAULT NULL, ADD tac_cookie_name VARCHAR(255) DEFAULT \'rgpd\', ADD tac_cookieslist TINYINT(1) DEFAULT 0 NOT NULL, ADD tac_custom_closer_id VARCHAR(255) DEFAULT NULL, ADD tac_deny_all_cta TINYINT(1) DEFAULT 1 NOT NULL, ADD tac_google_consent_mode TINYINT(1) DEFAULT 1 NOT NULL, ADD tac_group_services TINYINT(1) DEFAULT 1 NOT NULL, ADD tac_handle_browser_dntrequest TINYINT(1) DEFAULT 0 NOT NULL, ADD tac_hashtag VARCHAR(255) DEFAULT \'#rgpd\', ADD tac_high_privacy TINYINT(1) DEFAULT 1 NOT NULL, ADD tac_icon_position VARCHAR(255) DEFAULT NULL, ADD tac_mandatory TINYINT(1) DEFAULT 1 NOT NULL, ADD tac_mandatory_cta TINYINT(1) DEFAULT 0 NOT NULL, ADD tac_more_info_link TINYINT(1) DEFAULT 1 NOT NULL, ADD tac_orientation VARCHAR(255) DEFAULT \'middle\', ADD tac_partners_list TINYINT(1) DEFAULT 1 NOT NULL, ADD tac_privacy_url VARCHAR(255) DEFAULT NULL, ADD tac_readmore_link VARCHAR(255) DEFAULT NULL, ADD tac_remove_credit TINYINT(1) DEFAULT 0 NOT NULL, ADD tac_service_default_state VARCHAR(255) DEFAULT \'wait\', ADD tac_services VARCHAR(255) DEFAULT NULL, ADD tac_show_alert_small TINYINT(1) DEFAULT 1 NOT NULL, ADD tac_show_details_on_click TINYINT(1) DEFAULT 1 NOT NULL, ADD tac_show_icon TINYINT(1) DEFAULT 1 NOT NULL, ADD tac_use_external_css TINYINT(1) DEFAULT 0 NOT NULL, ADD tac_use_external_js TINYINT(1) DEFAULT 0 NOT NULL, CHANGE disable_empty_agent disable_empty_agent TINYINT(1) DEFAULT 0 NOT NULL, CHANGE user_link user_link TINYINT(1) DEFAULT 0 NOT NULL, CHANGE user_show user_show TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('DROP INDEX lookup_unique_idx ON ext_translations');
        $this->addSql('DROP INDEX general_translations_lookup_idx ON ext_translations');
        $this->addSql('DROP INDEX translations_lookup_idx ON ext_translations');
        $this->addSql('CREATE UNIQUE INDEX lookup_unique_idx ON ext_translations (foreign_key, locale, object_class, field)');
        $this->addSql('ALTER TABLE movie ADD adult TINYINT(1) NOT NULL, ADD certification VARCHAR(255) DEFAULT NULL, ADD citation VARCHAR(255) DEFAULT NULL, ADD countries JSON DEFAULT NULL, ADD file TINYINT(1) NOT NULL, ADD release_date DATE DEFAULT NULL, ADD tmdb VARCHAR(255) DEFAULT NULL, ADD saga_id CHAR(36) DEFAULT NULL, DROP color, DROP country, DROP year');
        $this->addSql('ALTER TABLE movie ADD CONSTRAINT FK_1D5EF26FB2CCEE2E FOREIGN KEY (saga_id) REFERENCES saga (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1D5EF26F85489131 ON movie (imdb)');
        $this->addSql('CREATE INDEX IDX_1D5EF26FB2CCEE2E ON movie (saga_id)');
        $this->addSql('CREATE INDEX IDX_PAGE_SLUG ON page (slug)');
        $this->addSql('ALTER TABLE paragraph ADD classes VARCHAR(255) DEFAULT NULL, ADD leftposition TINYINT(1) DEFAULT NULL, ADD refmovie_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD398623FCF0451 FOREIGN KEY (refmovie_id) REFERENCES movie (id)');
        $this->addSql('CREATE INDEX IDX_7DD398623FCF0451 ON paragraph (refmovie_id)');
        $this->addSql('CREATE INDEX IDX_POST_SLUG ON post (slug)');
        $this->addSql('ALTER TABLE star ADD img VARCHAR(255) DEFAULT NULL, ADD owner VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_STORY_SLUG ON story (slug)');
        $this->addSql('CREATE INDEX IDX_TAG_TYPE_SLUG ON tag (type, slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tag_movie DROP FOREIGN KEY FK_3FB2EB69BAD26311');
        $this->addSql('ALTER TABLE tag_movie DROP FOREIGN KEY FK_3FB2EB698F93B6FC');
        $this->addSql('DROP TABLE saga');
        $this->addSql('DROP TABLE tag_movie');
        $this->addSql('DROP INDEX IDX_BLOCK_SLUG ON block');
        $this->addSql('ALTER TABLE block DROP classes');
        $this->addSql('DROP INDEX IDX_CATEGORY_TYPE_SLUG ON category');
        $this->addSql('DROP INDEX IDX_CHAPTER_SLUG ON chapter');
        $this->addSql('ALTER TABLE configuration DROP tab_icon_src, DROP tac_accept_all_cta, DROP tac_adblocker, DROP tac_always_need_consent, DROP tac_body_position, DROP tac_close_popup, DROP tac_cookie_domain, DROP tac_cookie_name, DROP tac_cookieslist, DROP tac_custom_closer_id, DROP tac_deny_all_cta, DROP tac_google_consent_mode, DROP tac_group_services, DROP tac_handle_browser_dntrequest, DROP tac_hashtag, DROP tac_high_privacy, DROP tac_icon_position, DROP tac_mandatory, DROP tac_mandatory_cta, DROP tac_more_info_link, DROP tac_orientation, DROP tac_partners_list, DROP tac_privacy_url, DROP tac_readmore_link, DROP tac_remove_credit, DROP tac_service_default_state, DROP tac_services, DROP tac_show_alert_small, DROP tac_show_details_on_click, DROP tac_show_icon, DROP tac_use_external_css, DROP tac_use_external_js, CHANGE disable_empty_agent disable_empty_agent TINYINT(1) DEFAULT 1 NOT NULL, CHANGE user_link user_link TINYINT(1) DEFAULT 1 NOT NULL, CHANGE user_show user_show TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('DROP INDEX lookup_unique_idx ON ext_translations');
        $this->addSql('CREATE INDEX general_translations_lookup_idx ON ext_translations (object_class, foreign_key)');
        $this->addSql('CREATE INDEX translations_lookup_idx ON ext_translations (locale, object_class, foreign_key)');
        $this->addSql('CREATE UNIQUE INDEX lookup_unique_idx ON ext_translations (locale, object_class, field, foreign_key)');
        $this->addSql('ALTER TABLE movie DROP FOREIGN KEY FK_1D5EF26FB2CCEE2E');
        $this->addSql('DROP INDEX UNIQ_1D5EF26F85489131 ON movie');
        $this->addSql('DROP INDEX IDX_1D5EF26FB2CCEE2E ON movie');
        $this->addSql('ALTER TABLE movie ADD color VARCHAR(255) DEFAULT NULL, ADD country VARCHAR(255) DEFAULT NULL, ADD year INT DEFAULT NULL, DROP adult, DROP certification, DROP citation, DROP countries, DROP file, DROP release_date, DROP tmdb, DROP saga_id');
        $this->addSql('DROP INDEX IDX_PAGE_SLUG ON page');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD398623FCF0451');
        $this->addSql('DROP INDEX IDX_7DD398623FCF0451 ON paragraph');
        $this->addSql('ALTER TABLE paragraph DROP classes, DROP leftposition, DROP refmovie_id');
        $this->addSql('DROP INDEX IDX_POST_SLUG ON post');
        $this->addSql('ALTER TABLE star DROP img, DROP owner');
        $this->addSql('DROP INDEX IDX_STORY_SLUG ON story');
        $this->addSql('DROP INDEX IDX_TAG_TYPE_SLUG ON tag');
    }
}
