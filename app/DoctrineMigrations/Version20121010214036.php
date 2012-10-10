<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20121010214036 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        
        $this->addSql("CREATE TABLE Log (id INT AUTO_INCREMENT NOT NULL, item_id INT DEFAULT NULL, action VARCHAR(255) NOT NULL, happened DATETIME NOT NULL, old VARCHAR(255) DEFAULT NULL, INDEX IDX_B7722E25126F525E (item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE Log ADD CONSTRAINT FK_B7722E25126F525E FOREIGN KEY (item_id) REFERENCES Item (id)");
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        
        $this->addSql("DROP TABLE Log");
    }
}
