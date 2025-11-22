<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251122154137 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `group` (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE group_user (group_id INT NOT NULL, user_id CHAR(36) NOT NULL, INDEX IDX_A4C98D39FE54D947 (group_id), INDEX IDX_A4C98D39A76ED395 (user_id), PRIMARY KEY (group_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE permission (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE permission_group (permission_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_BB4729B6FED90CCA (permission_id), INDEX IDX_BB4729B6FE54D947 (group_id), PRIMARY KEY (permission_id, group_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE recommendation (id INT AUTO_INCREMENT NOT NULL, overview LONGTEXT DEFAULT NULL, poster VARCHAR(255) DEFAULT NULL, release_date DATE NOT NULL, title VARCHAR(255) NOT NULL, tmdb VARCHAR(255) DEFAULT NULL, refmovie_id CHAR(36) DEFAULT NULL, refsaga_id CHAR(36) DEFAULT NULL, refserie_id CHAR(36) DEFAULT NULL, INDEX IDX_433224D23FCF0451 (refmovie_id), INDEX IDX_433224D23EE66154 (refsaga_id), INDEX IDX_433224D2691F3A10 (refserie_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE group_user ADD CONSTRAINT FK_A4C98D39FE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE group_user ADD CONSTRAINT FK_A4C98D39A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE permission_group ADD CONSTRAINT FK_BB4729B6FED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE permission_group ADD CONSTRAINT FK_BB4729B6FE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recommendation ADD CONSTRAINT FK_433224D23FCF0451 FOREIGN KEY (refmovie_id) REFERENCES movie (id)');
        $this->addSql('ALTER TABLE recommendation ADD CONSTRAINT FK_433224D23EE66154 FOREIGN KEY (refsaga_id) REFERENCES saga (id)');
        $this->addSql('ALTER TABLE recommendation ADD CONSTRAINT FK_433224D2691F3A10 FOREIGN KEY (refserie_id) REFERENCES serie (id)');
        $this->addSql('ALTER TABLE company DROP json');
        $this->addSql('ALTER TABLE episode DROP json');
        $this->addSql('ALTER TABLE movie ADD poster VARCHAR(255) DEFAULT NULL, DROP json, CHANGE img backdrop VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE saga ADD poster VARCHAR(255) DEFAULT NULL, DROP json, CHANGE img backdrop VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE season DROP json, CHANGE img poster VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE serie ADD poster VARCHAR(255) DEFAULT NULL, DROP json, CHANGE img backdrop VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE group_user DROP FOREIGN KEY FK_A4C98D39FE54D947');
        $this->addSql('ALTER TABLE group_user DROP FOREIGN KEY FK_A4C98D39A76ED395');
        $this->addSql('ALTER TABLE permission_group DROP FOREIGN KEY FK_BB4729B6FED90CCA');
        $this->addSql('ALTER TABLE permission_group DROP FOREIGN KEY FK_BB4729B6FE54D947');
        $this->addSql('ALTER TABLE recommendation DROP FOREIGN KEY FK_433224D23FCF0451');
        $this->addSql('ALTER TABLE recommendation DROP FOREIGN KEY FK_433224D23EE66154');
        $this->addSql('ALTER TABLE recommendation DROP FOREIGN KEY FK_433224D2691F3A10');
        $this->addSql('DROP TABLE `group`');
        $this->addSql('DROP TABLE group_user');
        $this->addSql('DROP TABLE permission');
        $this->addSql('DROP TABLE permission_group');
        $this->addSql('DROP TABLE recommendation');
        $this->addSql('ALTER TABLE company ADD json JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE episode ADD json JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE movie ADD img VARCHAR(255) DEFAULT NULL, ADD json JSON DEFAULT NULL, DROP backdrop, DROP poster');
        $this->addSql('ALTER TABLE saga ADD img VARCHAR(255) DEFAULT NULL, ADD json JSON DEFAULT NULL, DROP backdrop, DROP poster');
        $this->addSql('ALTER TABLE season ADD json JSON DEFAULT NULL, CHANGE poster img VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE serie ADD img VARCHAR(255) DEFAULT NULL, ADD json JSON DEFAULT NULL, DROP backdrop, DROP poster');
    }
}
