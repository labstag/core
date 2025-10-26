<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251026123526 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE configuration ADD chapter_placeholder VARCHAR(255) DEFAULT NULL, ADD edito_placeholder VARCHAR(255) DEFAULT NULL, ADD episode_placeholder VARCHAR(255) DEFAULT NULL, ADD language_tmdb VARCHAR(255) DEFAULT NULL, ADD memo_placeholder VARCHAR(255) DEFAULT NULL, ADD movie_placeholder VARCHAR(255) DEFAULT NULL, ADD page_placeholder VARCHAR(255) DEFAULT NULL, ADD post_placeholder VARCHAR(255) DEFAULT NULL, ADD saga_placeholder VARCHAR(255) DEFAULT NULL, ADD season_placeholder VARCHAR(255) DEFAULT NULL, ADD serie_placeholder VARCHAR(255) DEFAULT NULL, ADD star_placeholder VARCHAR(255) DEFAULT NULL, ADD story_placeholder VARCHAR(255) DEFAULT NULL, ADD user_placeholder VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE configuration DROP chapter_placeholder, DROP edito_placeholder, DROP episode_placeholder, DROP language_tmdb, DROP memo_placeholder, DROP movie_placeholder, DROP page_placeholder, DROP post_placeholder, DROP saga_placeholder, DROP season_placeholder, DROP serie_placeholder, DROP star_placeholder, DROP story_placeholder, DROP user_placeholder');
    }
}
