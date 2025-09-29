<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250917073319 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paragraph ADD refmovie_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD398623FCF0451 FOREIGN KEY (refmovie_id) REFERENCES movie (id)');
        $this->addSql('CREATE INDEX IDX_7DD398623FCF0451 ON paragraph (refmovie_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD398623FCF0451');
        $this->addSql('DROP INDEX IDX_7DD398623FCF0451 ON paragraph');
        $this->addSql('ALTER TABLE paragraph DROP refmovie_id');
    }
}
