<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240729153635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE serene_result DROP CONSTRAINT fk_52a464d1243af005');
        $this->addSql('DROP INDEX uniq_52a464d1243af005');
        $this->addSql('ALTER TABLE serene_result ADD content TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE serene_result DROP sentence');
        $this->addSql('ALTER TABLE serene_result ALTER ia_answer TYPE TEXT');
        $this->addSql('ALTER TABLE serene_result RENAME COLUMN id_user_form_id TO user_id_id');
        $this->addSql('ALTER TABLE serene_result ADD CONSTRAINT FK_52A464D19D86650F FOREIGN KEY (user_id_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_52A464D19D86650F ON serene_result (user_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE serene_result DROP CONSTRAINT FK_52A464D19D86650F');
        $this->addSql('DROP INDEX IDX_52A464D19D86650F');
        $this->addSql('ALTER TABLE serene_result ADD sentence JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE serene_result DROP content');
        $this->addSql('ALTER TABLE serene_result ALTER ia_answer TYPE JSON');
        $this->addSql('ALTER TABLE serene_result RENAME COLUMN user_id_id TO id_user_form_id');
        $this->addSql('ALTER TABLE serene_result ADD CONSTRAINT fk_52a464d1243af005 FOREIGN KEY (id_user_form_id) REFERENCES user_form (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_52a464d1243af005 ON serene_result (id_user_form_id)');
    }
}
