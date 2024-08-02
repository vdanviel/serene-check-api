<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240731214430 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE serene_result DROP CONSTRAINT fk_52a464d19d86650f');
        $this->addSql('DROP INDEX idx_52a464d19d86650f');
        $this->addSql('ALTER TABLE serene_result RENAME COLUMN user_id_id TO user_id');
        $this->addSql('ALTER TABLE serene_result ADD CONSTRAINT FK_52A464D1A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_52A464D1A76ED395 ON serene_result (user_id)');
        $this->addSql('ALTER TABLE user_form DROP CONSTRAINT fk_2809b1869d86650f');
        $this->addSql('DROP INDEX idx_2809b1869d86650f');
        $this->addSql('ALTER TABLE user_form RENAME COLUMN user_id_id TO user_id');
        $this->addSql('ALTER TABLE user_form ADD CONSTRAINT FK_2809B186A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_2809B186A76ED395 ON user_form (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE serene_result DROP CONSTRAINT FK_52A464D1A76ED395');
        $this->addSql('DROP INDEX IDX_52A464D1A76ED395');
        $this->addSql('ALTER TABLE serene_result RENAME COLUMN user_id TO user_id_id');
        $this->addSql('ALTER TABLE serene_result ADD CONSTRAINT fk_52a464d19d86650f FOREIGN KEY (user_id_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_52a464d19d86650f ON serene_result (user_id_id)');
        $this->addSql('ALTER TABLE user_form DROP CONSTRAINT FK_2809B186A76ED395');
        $this->addSql('DROP INDEX IDX_2809B186A76ED395');
        $this->addSql('ALTER TABLE user_form RENAME COLUMN user_id TO user_id_id');
        $this->addSql('ALTER TABLE user_form ADD CONSTRAINT fk_2809b1869d86650f FOREIGN KEY (user_id_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_2809b1869d86650f ON user_form (user_id_id)');
    }
}
