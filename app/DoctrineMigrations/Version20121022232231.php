<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20121022232231 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        
        $this->addSql("ALTER TABLE Log ADD account_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE Log ADD CONSTRAINT FK_B7722E259B6B5FBA FOREIGN KEY (account_id) REFERENCES Account (id)");
        $this->addSql("CREATE INDEX IDX_B7722E259B6B5FBA ON Log (account_id)");
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");
        
        $this->addSql("ALTER TABLE Log DROP FOREIGN KEY FK_B7722E259B6B5FBA");
        $this->addSql("DROP INDEX IDX_B7722E259B6B5FBA ON Log");
        $this->addSql("ALTER TABLE Log DROP account_id");
    }
}
