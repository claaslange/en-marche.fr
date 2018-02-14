<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180425145459 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE adherent_email_subscription_history (
                id BIGINT AUTO_INCREMENT NOT NULL, 
                adherent_id INT UNSIGNED DEFAULT NULL, 
                referent_tag_id INT UNSIGNED DEFAULT NULL, 
                subscribed_emails_type VARCHAR(50) NOT NULL, 
                subscribed_at DATETIME NOT NULL, 
                unsubscribed_at DATETIME DEFAULT NULL, 
                INDEX IDX_272CB3E925F06C53 (adherent_id), 
                INDEX IDX_272CB3E99C262DB3 (referent_tag_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ');
        $this->addSql('ALTER TABLE adherent_email_subscription_history ADD CONSTRAINT FK_272CB3E925F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE adherent_email_subscription_history ADD CONSTRAINT FK_272CB3E99C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id)');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE adherent_email_subscription_history');
    }
}
