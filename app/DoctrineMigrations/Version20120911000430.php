<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
use Doctrine\DBAL\Schema\Schema;

class Version20120911000430 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        
        $this->addSql("CREATE TABLE Account (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, secret VARCHAR(255) NOT NULL, salt VARCHAR(255) NOT NULL, created DATETIME NOT NULL, modified DATETIME NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE TABLE ItemList (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, created DATETIME NOT NULL, modified DATETIME NOT NULL, PRIMARY KEY(id))");
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        
        $this->addSql("DROP TABLE Account");
        $this->addSql("DROP TABLE ItemList");
    }
}
