<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250823143418 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout du champ type dans la table work_period';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE work_period ADD type VARCHAR(32) NOT NULL DEFAULT 'work'
        SQL);

        // On force toutes les anciennes lignes à "work"
        $this->addSql(<<<'SQL'
            UPDATE work_period SET type = 'work' WHERE type IS NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE work_period DROP type
        SQL);
    }
}
