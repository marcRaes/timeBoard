<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250605124734 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout contrainte d\'unicité sur la combinaison de date et work_month_id dans la table work_day';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_9FCE7E0CAA9E377A ON work_day
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_9FCE7E0CAA9E377ADFB937B8 ON work_day (date, work_month_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_9FCE7E0CAA9E377ADFB937B8 ON work_day
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_9FCE7E0CAA9E377A ON work_day (date)
        SQL);
    }
}
