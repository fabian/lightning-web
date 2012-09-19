<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20120919215229 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql("ALTER TABLE AccountList DROP PRIMARY KEY, DROP id, CHANGE list_id list_id INT NOT NULL, CHANGE account_id account_id INT NOT NULL, ADD PRIMARY KEY (account_id, list_id)");
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql("ALTER TABLE AccountList DROP PRIMARY KEY, ADD id INT AUTO_INCREMENT NOT NULL, CHANGE account_id account_id INT DEFAULT NULL, CHANGE list_id list_id INT DEFAULT NULL, ADD PRIMARY KEY (id)");
    }
}
