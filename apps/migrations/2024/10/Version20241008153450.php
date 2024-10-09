<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241008153450 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paragraph ADD form_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', ADD html_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', ADD image_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', ADD text_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', ADD video_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD398625FF69B7D FOREIGN KEY (form_id) REFERENCES paragraph_form (id)');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD398623CD4754E FOREIGN KEY (html_id) REFERENCES paragraph_html (id)');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD398623DA5256D FOREIGN KEY (image_id) REFERENCES paragraph_image (id)');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD39862698D3548 FOREIGN KEY (text_id) REFERENCES paragraph_text (id)');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD3986229C1004E FOREIGN KEY (video_id) REFERENCES paragraph_video (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7DD398625FF69B7D ON paragraph (form_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7DD398623CD4754E ON paragraph (html_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7DD398623DA5256D ON paragraph (image_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7DD39862698D3548 ON paragraph (text_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7DD3986229C1004E ON paragraph (video_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD398625FF69B7D');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD398623CD4754E');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD398623DA5256D');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD39862698D3548');
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD3986229C1004E');
        $this->addSql('DROP INDEX UNIQ_7DD398625FF69B7D ON paragraph');
        $this->addSql('DROP INDEX UNIQ_7DD398623CD4754E ON paragraph');
        $this->addSql('DROP INDEX UNIQ_7DD398623DA5256D ON paragraph');
        $this->addSql('DROP INDEX UNIQ_7DD39862698D3548 ON paragraph');
        $this->addSql('DROP INDEX UNIQ_7DD3986229C1004E ON paragraph');
        $this->addSql('ALTER TABLE paragraph DROP form_id, DROP html_id, DROP image_id, DROP text_id, DROP video_id');
    }
}
