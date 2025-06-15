<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250615124924 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE defi_progress (id SERIAL NOT NULL, user_id_id INT DEFAULT NULL, defi_id INT DEFAULT NULL, date DATE NOT NULL, finish BOOLEAN NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E943D1B9D86650F ON defi_progress (user_id_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E943D1B73F00F27 ON defi_progress (defi_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE defi_progress ADD CONSTRAINT FK_E943D1B9D86650F FOREIGN KEY (user_id_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE defi_progress ADD CONSTRAINT FK_E943D1B73F00F27 FOREIGN KEY (defi_id) REFERENCES defi (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE defi_progress DROP CONSTRAINT FK_E943D1B9D86650F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE defi_progress DROP CONSTRAINT FK_E943D1B73F00F27
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE defi_progress
        SQL);
    }
}
