<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20120929201224 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        
        $this->addSql("CREATE TABLE Item (id INT AUTO_INCREMENT NOT NULL, list_id INT DEFAULT NULL, value VARCHAR(255) NOT NULL, done TINYINT(1) NOT NULL, deleted TINYINT(1) NOT NULL, created DATETIME NOT NULL, modified DATETIME NOT NULL, INDEX IDX_BF298A203DAE168B (list_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE Item ADD CONSTRAINT FK_BF298A203DAE168B FOREIGN KEY (list_id) REFERENCES ItemList (id)");
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        
        $this->addSql("DROP TABLE Item");
    }
}
