<?php

namespace Runalyze\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * database cleanup
 */
class Version20161027173150 extends AbstractMigration implements ContainerAwareInterface
{
    /** @var ContainerInterface|null */
    private $container;

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        //Table: conf
        $prefix = $this->container->getParameter('database_prefix');
        $this->addSql('DELETE FROM `'.$prefix.'conf` WHERE `accountid` = \'-1\'');
        $this->addSql('ALTER TABLE `'.$prefix.'conf`
                  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  MODIFY `accountid` int(10) unsigned NOT NULL');

        //Table: plugin
        $this->addSql('DELETE FROM `'.$prefix.'plugin` WHERE `accountid` = \'-1\'');
        $this->addSql('ALTER TABLE `'.$prefix.'plugin`
                  MODIFY `active` tinyint(1) unsigned NOT NULL DEFAULT \'1\',
                  MODIFY `order` tinyint unsigned NOT NULL DEFAULT \'0\',
                  MODIFY `accountid` int(10) unsigned NOT NULL');

        //Table: equipment_type
        $this->addSql('ALTER TABLE `'.$prefix.'equipment_type`
                  MODIFY `input` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
                  MODIFY `max_km` mediumint unsigned DEFAULT NULL,
                  MODIFY `max_time` mediumint unsigned DEFAULT NULL');
        $this->addSql('UPDATE `'.$prefix.'equipment_type` SET `max_km` = NULL WHERE `max_km`= \'0\'');
        $this->addSql('UPDATE `'.$prefix.'equipment_type` SET `max_time` = NULL WHERE `max_time`= \'0\'');

        //Table: type
        $this->addSql('DELETE FROM `'.$prefix.'type` WHERE `accountid` = \'-1\'');
        $this->addSql('DELETE FROM `'.$prefix.'type` WHERE `sportid` = \'0\'');
        $this->addSql('ALTER TABLE `'.$prefix.'type`
                  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  MODIFY `sportid` int(10) unsigned NOT NULL,
                  MODIFY `accountid` int(10) unsigned NOT NULL');

        //Table: user
        $this->addSql('DELETE FROM `'.$prefix.'user` WHERE `accountid` = \'-1\'');
        $this->addSql('ALTER TABLE '.$prefix.'user`
                  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  MODIFY `accountid` int(10) unsigned NOT NULL,
                  MODIFY `time` int(10) unsigned NOT NULL,
                  MODIFY `pulse_rest` tinyint unsigned DEFAULT NULL,
                  MODIFY `pulse_max` tinyint unsigned DEFAULT NULL,
                  MODIFY `fat` decimal(3,1) DEFAULT NULL,
                  MODIFY `water` decimal(3,1) DEFAULT NULL,
                  MODIFY `muscles` decimal(3,1) DEFAULT NULL,
                  MODIFY `weight` decimal(5,2) DEFAULT NULL,
                  MODIFY `sleep_duration` smallint(3) unsigned DEFAULT NULL');
        $this->addSql('UPDATE `'.$prefix.'user` SET `pulse_rest` = NULL WHERE `pulse_rest`= \'0\'');
        $this->addSql('UPDATE `'.$prefix.'user` SET `pulse_max` = NULL WHERE `pulse_max`= \'0\'');
        $this->addSql('UPDATE `'.$prefix.'user` SET `fat` = NULL WHERE `fat`= \'0.0\'');
        $this->addSql('UPDATE `'.$prefix.'user` SET `weight` = NULL WHERE `weight`= \'0.0\'');
        $this->addSql('UPDATE `'.$prefix.'user` SET `water` = NULL WHERE `water`= \'0.0\'');
        $this->addSql('UPDATE `'.$prefix.'user` SET `muscles` = NULL WHERE `muscles`= \'0.0\'');
        $this->addSql('UPDATE `'.$prefix.'user` SET `sleep_duration` = NULL WHERE `sleep_duration`= \'0\'');

        //Table account
        $this->addSql('ALTER TABLE '.$prefix.'account`
                  MODIFY `registerdate` int(10) unsigned DEFAULT NULL,
                  MODIFY `lastaction` int(10) unsigned DEFAULT NULL,
                  MODIFY `lastlogin` int(10) unsigned DEFAULT NULL,
                  MODIFY `activation_hash` varchar(32) DEFAULT NULL,
                  MODIFY `deletion_hash` varchar(32) DEFAULT NULL,
                  MODIFY `changepw_timelimit` int(10) unsigned DEFAULT NULL,
                  MODIFY `changepw_hash` varchar(32) DEFAULT NULL,
                  MODIFY `allow_mails` tinyint(1) unsigned NOT NULL DEFAULT \'1\'');
        $this->addSql('UPDATE `'.$prefix.'account` SET `registerdate` = NULL WHERE `registerdate`= \'0\'');
        $this->addSql('UPDATE `'.$prefix.'account` SET `lastaction` = NULL WHERE `lastaction`= \'0\'');
        $this->addSql('UPDATE `'.$prefix.'account` SET `lastlogin` = NULL WHERE `lastlogin`= \'0\'');
        $this->addSql('UPDATE `'.$prefix.'account` SET `activation_hash` = NULL WHERE `activation_hash`= \'\'');
        $this->addSql('UPDATE `'.$prefix.'account` SET `deletion_hash` = NULL WHERE `deletion_hash`= \'\'');
        $this->addSql('UPDATE `'.$prefix.'account` SET `changepw_timelimit` = NULL WHERE `changepw_timelimit`= \'0\'');
        $this->addSql('UPDATE `'.$prefix.'account` SET `changepw_hash` = NULL WHERE `changepw_hash`= \'\'');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {

    }
}
