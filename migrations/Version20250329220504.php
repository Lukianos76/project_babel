<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250329220504 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE api_key (id UUID NOT NULL, token VARCHAR(64) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, revoked BOOLEAN NOT NULL, user_id UUID NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C912ED9DA76ED395 ON api_key (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE password_reset_token (id UUID NOT NULL, token VARCHAR(64) NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, used BOOLEAN NOT NULL, user_id UUID NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_6B7BA4B65F37A13B ON password_reset_token (token)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6B7BA4B6A76ED395 ON password_reset_token (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE refresh_token (id UUID NOT NULL, token VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, revoked BOOLEAN NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, user_agent VARCHAR(255) DEFAULT NULL, user_id UUID NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_C74F21955F37A13B ON refresh_token (token)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C74F2195A76ED395 ON refresh_token (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE "user" (id UUID NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE api_key ADD CONSTRAINT FK_C912ED9DA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE password_reset_token ADD CONSTRAINT FK_6B7BA4B6A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE refresh_token ADD CONSTRAINT FK_C74F2195A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE api_key DROP CONSTRAINT FK_C912ED9DA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE password_reset_token DROP CONSTRAINT FK_6B7BA4B6A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE refresh_token DROP CONSTRAINT FK_C74F2195A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE api_key
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE password_reset_token
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE refresh_token
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE "user"
        SQL);
    }
}
