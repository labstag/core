<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251127103204 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE franchise (id CHAR(36) NOT NULL, slug VARCHAR(255) DEFAULT NULL, igdb VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_66F6CE2A989D9B62 (slug), INDEX IDX_FRANCHISE_SLUG (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE game (id CHAR(36) NOT NULL, img VARCHAR(255) DEFAULT NULL, slug VARCHAR(255) DEFAULT NULL, artworks JSON DEFAULT NULL, igdb VARCHAR(255) NOT NULL, release_date DATE DEFAULT NULL, screenshots JSON DEFAULT NULL, title VARCHAR(255) NOT NULL, url VARCHAR(255) DEFAULT NULL, videos JSON DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_232B318C989D9B62 (slug), INDEX IDX_GAME_SLUG (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE game_game_category (game_id CHAR(36) NOT NULL, game_category_id CHAR(36) NOT NULL, INDEX IDX_7EC7A8CE48FD905 (game_id), INDEX IDX_7EC7A8CCC13DFE0 (game_category_id), PRIMARY KEY (game_id, game_category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE game_franchise (game_id CHAR(36) NOT NULL, franchise_id CHAR(36) NOT NULL, INDEX IDX_B4500F28E48FD905 (game_id), INDEX IDX_B4500F28523CAB89 (franchise_id), PRIMARY KEY (game_id, franchise_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE game_platform (game_id CHAR(36) NOT NULL, platform_id CHAR(36) NOT NULL, INDEX IDX_92162FEDE48FD905 (game_id), INDEX IDX_92162FEDFFE6496F (platform_id), PRIMARY KEY (game_id, platform_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE platform (id CHAR(36) NOT NULL, img VARCHAR(255) DEFAULT NULL, slug VARCHAR(255) DEFAULT NULL, abbreviation VARCHAR(255) NOT NULL, family VARCHAR(255) DEFAULT NULL, generation INT NOT NULL, igdb VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_3952D0CB989D9B62 (slug), INDEX IDX_PLATFORM_SLUG (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE game_game_category ADD CONSTRAINT FK_7EC7A8CE48FD905 FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_game_category ADD CONSTRAINT FK_7EC7A8CCC13DFE0 FOREIGN KEY (game_category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_franchise ADD CONSTRAINT FK_B4500F28E48FD905 FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_franchise ADD CONSTRAINT FK_B4500F28523CAB89 FOREIGN KEY (franchise_id) REFERENCES franchise (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_platform ADD CONSTRAINT FK_92162FEDE48FD905 FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_platform ADD CONSTRAINT FK_92162FEDFFE6496F FOREIGN KEY (platform_id) REFERENCES platform (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_game_category DROP FOREIGN KEY FK_7EC7A8CE48FD905');
        $this->addSql('ALTER TABLE game_game_category DROP FOREIGN KEY FK_7EC7A8CCC13DFE0');
        $this->addSql('ALTER TABLE game_franchise DROP FOREIGN KEY FK_B4500F28E48FD905');
        $this->addSql('ALTER TABLE game_franchise DROP FOREIGN KEY FK_B4500F28523CAB89');
        $this->addSql('ALTER TABLE game_platform DROP FOREIGN KEY FK_92162FEDE48FD905');
        $this->addSql('ALTER TABLE game_platform DROP FOREIGN KEY FK_92162FEDFFE6496F');
        $this->addSql('DROP TABLE franchise');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE game_game_category');
        $this->addSql('DROP TABLE game_franchise');
        $this->addSql('DROP TABLE game_platform');
        $this->addSql('DROP TABLE platform');
    }
}
