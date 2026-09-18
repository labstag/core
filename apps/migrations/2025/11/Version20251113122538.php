<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251113122538 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY `FK_7DD398623FCF0451`');
        $this->addSql('ALTER TABLE paragraph ADD skills JSON DEFAULT NULL, ADD trainings JSON DEFAULT NULL, DROP competences, DROP experiences, DROP formations');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT FK_7DD398623FCF0451 FOREIGN KEY (refmovie_id) REFERENCES movie (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paragraph DROP FOREIGN KEY FK_7DD398623FCF0451');
        $this->addSql('ALTER TABLE paragraph ADD competences JSON DEFAULT NULL, ADD experiences JSON DEFAULT NULL, ADD formations JSON DEFAULT NULL, DROP skills, DROP trainings');
        $this->addSql('ALTER TABLE paragraph ADD CONSTRAINT `FK_7DD398623FCF0451` FOREIGN KEY (refmovie_id) REFERENCES movie (id)');
    }
}
