<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250525130740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table work_period';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE work_period (id INT AUTO_INCREMENT NOT NULL, work_day_id INT NOT NULL, label VARCHAR(50) NOT NULL, time_start TIME DEFAULT NULL COMMENT '(DC2Type:time_immutable)', time_end TIME DEFAULT NULL COMMENT '(DC2Type:time_immutable)', duration INT DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, replaced_agent VARCHAR(255) DEFAULT NULL, INDEX IDX_6E41D8ECA23B8704 (work_day_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE work_period ADD CONSTRAINT FK_6E41D8ECA23B8704 FOREIGN KEY (work_day_id) REFERENCES work_day (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE work_period DROP FOREIGN KEY FK_6E41D8ECA23B8704
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE work_period
        SQL);
    }
}
