<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250525125957 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table work_day';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE work_day (id INT AUTO_INCREMENT NOT NULL, work_month_id INT NOT NULL, date DATE NOT NULL COMMENT '(DC2Type:date_immutable)', is_full_day TINYINT(1) NOT NULL, has_lunch_ticket TINYINT(1) NOT NULL, INDEX IDX_9FCE7E0CDFB937B8 (work_month_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE work_day ADD CONSTRAINT FK_9FCE7E0CDFB937B8 FOREIGN KEY (work_month_id) REFERENCES work_month (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE work_day DROP FOREIGN KEY FK_9FCE7E0CDFB937B8
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE work_day
        SQL);
    }
}
