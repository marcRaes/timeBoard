<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250604150936 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE work_day DROP is_full_day
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE work_period CHANGE time_start time_start TIME NOT NULL COMMENT '(DC2Type:time_immutable)', CHANGE time_end time_end TIME NOT NULL COMMENT '(DC2Type:time_immutable)', CHANGE duration duration INT NOT NULL, CHANGE location location VARCHAR(255) NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE work_period CHANGE time_start time_start TIME DEFAULT NULL COMMENT '(DC2Type:time_immutable)', CHANGE time_end time_end TIME DEFAULT NULL COMMENT '(DC2Type:time_immutable)', CHANGE duration duration INT DEFAULT NULL, CHANGE location location VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE work_day ADD is_full_day TINYINT(1) NOT NULL
        SQL);
    }
}
