<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20120914191144 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql("CREATE TABLE AccountList (id INT AUTO_INCREMENT NOT NULL, account_id INT DEFAULT NULL, list_id INT DEFAULT NULL, permission VARCHAR(255) NOT NULL, deleted TINYINT(1) NOT NULL, `read` DATETIME NOT NULL, pushed DATETIME NOT NULL, created DATETIME NOT NULL, modified DATETIME NOT NULL, INDEX IDX_6FF698559B6B5FBA (account_id), INDEX IDX_6FF698553DAE168B (list_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE AccountList ADD CONSTRAINT FK_6FF698559B6B5FBA FOREIGN KEY (account_id) REFERENCES Account (id)");
        $this->addSql("ALTER TABLE AccountList ADD CONSTRAINT FK_6FF698553DAE168B FOREIGN KEY (list_id) REFERENCES ItemList (id)");
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql("DROP TABLE AccountList");
    }
}
