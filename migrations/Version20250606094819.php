<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250606094819 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table work_report_submission';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE work_report_submission (id INT AUTO_INCREMENT NOT NULL, work_month_id INT NOT NULL, sent_on DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', recipient_email VARCHAR(255) NOT NULL, pdf_path VARCHAR(255) NOT NULL, attachment_path VARCHAR(255) DEFAULT NULL, status VARCHAR(20) NOT NULL, error_message VARCHAR(255) DEFAULT NULL, INDEX IDX_AE64B023DFB937B8 (work_month_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE work_report_submission ADD CONSTRAINT FK_AE64B023DFB937B8 FOREIGN KEY (work_month_id) REFERENCES work_month (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE work_report_submission DROP FOREIGN KEY FK_AE64B023DFB937B8
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE work_report_submission
        SQL);
    }
}
