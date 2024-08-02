<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240727131146 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE user_answer_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE serene_result_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_form_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE serene_result (id INT NOT NULL, id_user_form_id INT DEFAULT NULL, sentence JSON DEFAULT NULL, ia_answer JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_52A464D1243AF005 ON serene_result (id_user_form_id)');
        $this->addSql('CREATE TABLE user_form (id INT NOT NULL, user_id INT DEFAULT NULL, question VARCHAR(255) DEFAULT NULL, answer TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2809B1869D86650F ON user_form (user_id)');
        $this->addSql('ALTER TABLE serene_result ADD CONSTRAINT FK_52A464D1243AF005 FOREIGN KEY (id_user_form_id) REFERENCES user_form (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_form ADD CONSTRAINT FK_2809B1869D86650F FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_answer DROP CONSTRAINT fk_bf8f511879f37ae5');
        $this->addSql('DROP TABLE user_answer');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE serene_result_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_form_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE user_answer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_answer (id INT NOT NULL, id_user INT DEFAULT NULL, answer VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_bf8f511879f37ae5 ON user_answer (id_user)');
        $this->addSql('ALTER TABLE user_answer ADD CONSTRAINT fk_bf8f511879f37ae5 FOREIGN KEY (id_user) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE serene_result DROP CONSTRAINT FK_52A464D1243AF005');
        $this->addSql('ALTER TABLE user_form DROP CONSTRAINT FK_2809B1869D86650F');
        $this->addSql('DROP TABLE serene_result');
        $this->addSql('DROP TABLE user_form');
    }
}
