<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250611205736 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE suivi_objective (id SERIAL NOT NULL, objective_id INT DEFAULT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D0360A6F73484933 ON suivi_objective (objective_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE suivi_objective ADD CONSTRAINT FK_D0360A6F73484933 FOREIGN KEY (objective_id) REFERENCES objectives (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE suivi_objective DROP CONSTRAINT FK_D0360A6F73484933
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE suivi_objective
        SQL);
    }
}
