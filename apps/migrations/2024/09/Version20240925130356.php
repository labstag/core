<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240925130356 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chapter ADD meta_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE chapter ADD CONSTRAINT FK_F981B52E39FCA6F9 FOREIGN KEY (meta_id) REFERENCES meta (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F981B52E39FCA6F9 ON chapter (meta_id)');
        $this->addSql('ALTER TABLE edito ADD meta_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE edito ADD CONSTRAINT FK_F2EC5FE039FCA6F9 FOREIGN KEY (meta_id) REFERENCES meta (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F2EC5FE039FCA6F9 ON edito (meta_id)');
        $this->addSql('ALTER TABLE history ADD meta_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE history ADD CONSTRAINT FK_27BA704B39FCA6F9 FOREIGN KEY (meta_id) REFERENCES meta (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_27BA704B39FCA6F9 ON history (meta_id)');
        $this->addSql('ALTER TABLE meta DROP FOREIGN KEY FK_D7F21435579F4768');
        $this->addSql('ALTER TABLE meta DROP FOREIGN KEY FK_D7F214351E058452');
        $this->addSql('ALTER TABLE meta DROP FOREIGN KEY FK_D7F214355B3CFAAA');
        $this->addSql('ALTER TABLE meta DROP FOREIGN KEY FK_D7F214354B89032C');
        $this->addSql('ALTER TABLE meta DROP FOREIGN KEY FK_D7F21435C4663E4');
        $this->addSql('DROP INDEX IDX_D7F214351E058452 ON meta');
        $this->addSql('DROP INDEX IDX_D7F21435C4663E4 ON meta');
        $this->addSql('DROP INDEX IDX_D7F21435579F4768 ON meta');
        $this->addSql('DROP INDEX IDX_D7F214354B89032C ON meta');
        $this->addSql('DROP INDEX IDX_D7F214355B3CFAAA ON meta');
        $this->addSql('ALTER TABLE meta DROP chapter_id, DROP edito_id, DROP history_id, DROP page_id, DROP post_id');
        $this->addSql('ALTER TABLE page ADD meta_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB62039FCA6F9 FOREIGN KEY (meta_id) REFERENCES meta (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_140AB62039FCA6F9 ON page (meta_id)');
        $this->addSql('ALTER TABLE post ADD meta_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D39FCA6F9 FOREIGN KEY (meta_id) REFERENCES meta (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5A8A6C8D39FCA6F9 ON post (meta_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE edito DROP FOREIGN KEY FK_F2EC5FE039FCA6F9');
        $this->addSql('DROP INDEX UNIQ_F2EC5FE039FCA6F9 ON edito');
        $this->addSql('ALTER TABLE edito DROP meta_id');
        $this->addSql('ALTER TABLE chapter DROP FOREIGN KEY FK_F981B52E39FCA6F9');
        $this->addSql('DROP INDEX UNIQ_F981B52E39FCA6F9 ON chapter');
        $this->addSql('ALTER TABLE chapter DROP meta_id');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D39FCA6F9');
        $this->addSql('DROP INDEX UNIQ_5A8A6C8D39FCA6F9 ON post');
        $this->addSql('ALTER TABLE post DROP meta_id');
        $this->addSql('ALTER TABLE meta ADD chapter_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', ADD edito_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', ADD history_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', ADD page_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', ADD post_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE meta ADD CONSTRAINT FK_D7F21435579F4768 FOREIGN KEY (chapter_id) REFERENCES chapter (id)');
        $this->addSql('ALTER TABLE meta ADD CONSTRAINT FK_D7F214351E058452 FOREIGN KEY (history_id) REFERENCES history (id)');
        $this->addSql('ALTER TABLE meta ADD CONSTRAINT FK_D7F214355B3CFAAA FOREIGN KEY (edito_id) REFERENCES edito (id)');
        $this->addSql('ALTER TABLE meta ADD CONSTRAINT FK_D7F214354B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE meta ADD CONSTRAINT FK_D7F21435C4663E4 FOREIGN KEY (page_id) REFERENCES page (id)');
        $this->addSql('CREATE INDEX IDX_D7F214351E058452 ON meta (history_id)');
        $this->addSql('CREATE INDEX IDX_D7F21435C4663E4 ON meta (page_id)');
        $this->addSql('CREATE INDEX IDX_D7F21435579F4768 ON meta (chapter_id)');
        $this->addSql('CREATE INDEX IDX_D7F214354B89032C ON meta (post_id)');
        $this->addSql('CREATE INDEX IDX_D7F214355B3CFAAA ON meta (edito_id)');
        $this->addSql('ALTER TABLE history DROP FOREIGN KEY FK_27BA704B39FCA6F9');
        $this->addSql('DROP INDEX UNIQ_27BA704B39FCA6F9 ON history');
        $this->addSql('ALTER TABLE history DROP meta_id');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB62039FCA6F9');
        $this->addSql('DROP INDEX UNIQ_140AB62039FCA6F9 ON page');
        $this->addSql('ALTER TABLE page DROP meta_id');
    }
}
