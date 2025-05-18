<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250517224648 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE badges (id SERIAL NOT NULL, title VARCHAR(255) NOT NULL, description TEXT NOT NULL, type_critere VARCHAR(255) NOT NULL, obtained BOOLEAN NOT NULL, icon VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE badges_users (badges_id INT NOT NULL, users_id INT NOT NULL, PRIMARY KEY(badges_id, users_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A41AB9D8538DA1D0 ON badges_users (badges_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A41AB9D867B3B43D ON badges_users (users_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE defi (id SERIAL NOT NULL, title VARCHAR(255) NOT NULL, description TEXT NOT NULL, date_start DATE NOT NULL, date_end DATE NOT NULL, create_by VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE defi_users (id SERIAL NOT NULL, users_id_id INT DEFAULT NULL, defi_id_id INT DEFAULT NULL, point INT NOT NULL, ranking INT NOT NULL, date_inscription DATE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_AA1404B98333A1E ON defi_users (users_id_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_AA1404B61667BF5 ON defi_users (defi_id_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE habits (id SERIAL NOT NULL, users_id_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description TEXT NOT NULL, frequence INT NOT NULL, statut VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A541213A98333A1E ON habits (users_id_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE notifications (id SERIAL NOT NULL, users_id_id INT DEFAULT NULL, message TEXT NOT NULL, date_send DATE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6000B0D398333A1E ON notifications (users_id_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE objectives (id SERIAL NOT NULL, users_id_id INT DEFAULT NULL, titre VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, date_start DATE NOT NULL, date_end DATE NOT NULL, progres DOUBLE PRECISION NOT NULL, target_value DOUBLE PRECISION NOT NULL, current_value DOUBLE PRECISION NOT NULL, statut VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6CB0696C98333A1E ON objectives (users_id_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE suivihabits (id SERIAL NOT NULL, habits_id_id INT DEFAULT NULL, date DATE NOT NULL, finish BOOLEAN NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8C75ED855027F067 ON suivihabits (habits_id_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE badges_users ADD CONSTRAINT FK_A41AB9D8538DA1D0 FOREIGN KEY (badges_id) REFERENCES badges (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE badges_users ADD CONSTRAINT FK_A41AB9D867B3B43D FOREIGN KEY (users_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE defi_users ADD CONSTRAINT FK_AA1404B98333A1E FOREIGN KEY (users_id_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE defi_users ADD CONSTRAINT FK_AA1404B61667BF5 FOREIGN KEY (defi_id_id) REFERENCES defi (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE habits ADD CONSTRAINT FK_A541213A98333A1E FOREIGN KEY (users_id_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D398333A1E FOREIGN KEY (users_id_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE objectives ADD CONSTRAINT FK_6CB0696C98333A1E FOREIGN KEY (users_id_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE suivihabits ADD CONSTRAINT FK_8C75ED855027F067 FOREIGN KEY (habits_id_id) REFERENCES habits (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE badges_users DROP CONSTRAINT FK_A41AB9D8538DA1D0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE badges_users DROP CONSTRAINT FK_A41AB9D867B3B43D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE defi_users DROP CONSTRAINT FK_AA1404B98333A1E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE defi_users DROP CONSTRAINT FK_AA1404B61667BF5
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE habits DROP CONSTRAINT FK_A541213A98333A1E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notifications DROP CONSTRAINT FK_6000B0D398333A1E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE objectives DROP CONSTRAINT FK_6CB0696C98333A1E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE suivihabits DROP CONSTRAINT FK_8C75ED855027F067
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE badges
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE badges_users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE defi
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE defi_users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE habits
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE notifications
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE objectives
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE suivihabits
        SQL);
    }
}
