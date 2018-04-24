<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180423160911 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE committees_membership_events (
            uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
            committee_id INT UNSIGNED DEFAULT NULL,
            adherent_id INT UNSIGNED DEFAULT NULL,
            tag_id INT UNSIGNED DEFAULT NULL,
            action VARCHAR(10) NOT NULL,
            privilege VARCHAR(10) NOT NULL,
            date DATETIME NOT NULL,
            INDEX IDX_F0DEDF16ED1A100B (committee_id),
            INDEX IDX_F0DEDF1625F06C53 (adherent_id),
            INDEX IDX_F0DEDF16BAD26311 (tag_id),
            PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql('ALTER TABLE committees_membership_events ADD CONSTRAINT FK_F0DEDF16ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id)');
        $this->addSql('ALTER TABLE committees_membership_events ADD CONSTRAINT FK_F0DEDF1625F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE committees_membership_events ADD CONSTRAINT FK_F0DEDF16BAD26311 FOREIGN KEY (tag_id) REFERENCES referent_tags (id)');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE committees_membership_events');
    }
}
