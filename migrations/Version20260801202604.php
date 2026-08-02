<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260801202604 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create cookie_consent and lead tables (MySQL)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS cookie_consent (id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, ip VARCHAR(255) DEFAULT NULL, user_agent VARCHAR(1024) DEFAULT NULL, preferences JSON NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS `lead` (id BINARY(16) NOT NULL, source VARCHAR(512) DEFAULT NULL, phone VARCHAR(512) DEFAULT NULL, email VARCHAR(512) DEFAULT NULL, name VARCHAR(512) DEFAULT NULL, message VARCHAR(512) DEFAULT NULL, created_at DATETIME NOT NULL, payload JSON NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE cookie_consent');
        $this->addSql('DROP TABLE `lead`');
    }
}
