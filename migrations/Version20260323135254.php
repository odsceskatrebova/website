<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260323135254 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE IF NOT EXISTS cookie_consent (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, ip VARCHAR(255) DEFAULT NULL, user_agent VARCHAR(1024) DEFAULT NULL, preferences JSON NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE IF NOT EXISTS lead (id UUID NOT NULL, source VARCHAR(512) DEFAULT NULL, phone VARCHAR(512) DEFAULT NULL, email VARCHAR(512) DEFAULT NULL, name VARCHAR(512) DEFAULT NULL, message VARCHAR(512) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, payload JSON DEFAULT \'{}\' NOT NULL, PRIMARY KEY (id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE cookie_consent');
        $this->addSql('DROP TABLE lead');
    }
}
