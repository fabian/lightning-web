<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20120923224244 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql("CREATE TABLE AccessToken (id INT AUTO_INCREMENT NOT NULL, account_id INT DEFAULT NULL, challenge VARCHAR(255) NOT NULL, created DATETIME NOT NULL, approved TINYINT(1) NOT NULL, INDEX IDX_B39617F59B6B5FBA (account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE AccessToken ADD CONSTRAINT FK_B39617F59B6B5FBA FOREIGN KEY (account_id) REFERENCES Account (id)");
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql("DROP TABLE AccessToken");
    }
}
