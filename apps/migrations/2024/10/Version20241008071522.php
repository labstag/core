<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241008071522 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE paragraph (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', chapter_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', edito_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', history_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', memo_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', page_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', post_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', position INT NOT NULL, enable TINYINT(1) DEFAULT 1 NOT NULL, type VARCHAR(255) NOT NULL, deleted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_7DD39862579F4768 (chapter_id), INDEX IDX_7DD398625B3CFAAA (edito_id), INDEX IDX_7DD398621E058452 (history_id), INDEX IDX_7DD39862B4D32439 (memo_id), INDEX IDX_7DD39862C4663E4 (page_id), INDEX IDX_7DD398624B89032C (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD39862579F4768 FOREIGN KEY (chapter_id) REFERENCES chapter (id)');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD398625B3CFAAA FOREIGN KEY (edito_id) REFERENCES edito (id)');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD398621E058452 FOREIGN KEY (history_id) REFERENCES history (id)');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD39862B4D32439 FOREIGN KEY (memo_id) REFERENCES memo (id)');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD39862C4663E4 FOREIGN KEY (page_id) REFERENCES page (id)');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD398624B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD39862579F4768');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD398625B3CFAAA');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD398621E058452');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD39862B4D32439');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD39862C4663E4');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD398624B89032C');
        $this->addSql('DROP TABLE paragraph');
    }
}
