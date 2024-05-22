<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240521211428 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tests DROP FOREIGN KEY FK_1260FC5E166D1F9C');
        $this->addSql('DROP TABLE tests');
        $this->addSql('ALTER TABLE automate ADD project_id INT NOT NULL, ADD name VARCHAR(255) NOT NULL, ADD description_short VARCHAR(255) NOT NULL, ADD description LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', ADD type VARCHAR(255) NOT NULL, ADD duration BIGINT DEFAULT NULL, ADD status INT NOT NULL');
        $this->addSql('ALTER TABLE automate ADD CONSTRAINT FK_BA308867166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id)');
        $this->addSql('CREATE INDEX IDX_BA308867166D1F9C ON automate (project_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tests (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, status INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, duration BIGINT DEFAULT NULL, INDEX IDX_1260FC5E166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE tests ADD CONSTRAINT FK_1260FC5E166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id)');
        $this->addSql('ALTER TABLE automate DROP FOREIGN KEY FK_BA308867166D1F9C');
        $this->addSql('DROP INDEX IDX_BA308867166D1F9C ON automate');
        $this->addSql('ALTER TABLE automate DROP project_id, DROP name, DROP description_short, DROP description, DROP type, DROP duration, DROP status');
    }
}
