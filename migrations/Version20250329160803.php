<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250329160803 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE password_reset_token ALTER id TYPE UUID
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE password_reset_token ALTER id DROP IDENTITY
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE password_reset_token ALTER token TYPE VARCHAR(64)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE password_reset_token ALTER id TYPE INT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE password_reset_token ALTER id ADD GENERATED BY DEFAULT AS IDENTITY
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE password_reset_token ALTER token TYPE UUID
        SQL);
    }
}
