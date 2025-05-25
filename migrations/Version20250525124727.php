<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250525124727 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table work_month';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE work_month (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, month INT NOT NULL, year INT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_A64D6B29A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE work_month ADD CONSTRAINT FK_A64D6B29A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE work_month DROP FOREIGN KEY FK_A64D6B29A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE work_month
        SQL);
    }
}
