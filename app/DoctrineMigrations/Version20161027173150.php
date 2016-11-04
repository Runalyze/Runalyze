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
                  MODIFY `id` int(10) unsigned auto_increment NOT NULL,
                  MODIFY `accountid` int(10) unsigned NOT NULL');

        //Table: plugin
        $this->addSql('DELETE FROM `'.$prefix.'plugin` WHERE `accountid` = \'-1\'');
        $this->addSql('UPDATE `'.$prefix.'plugin` SET `order`= 0 WHERE `order` < 0');

        $this->addSql('ALTER TABLE `'.$prefix.'plugin`
                  MODIFY `active` tinyint(1) unsigned NOT NULL DEFAULT 1,
                  MODIFY `order` tinyint unsigned NOT NULL DEFAULT 0,
                  MODIFY `accountid` int(10) unsigned NOT NULL');

        //Table: equipment_type
        $this->addSql('UPDATE `'.$prefix.'equipment_type` SET `max_km` = 0 WHERE `max_km` > \'16777215\'');
        $this->addSql('UPDATE `'.$prefix.'equipment_type` SET `max_time` = 0 WHERE `max_time` > \'16777215\'');


        $this->addSql('ALTER TABLE `'.$prefix.'equipment_type`
                  MODIFY `input` tinyint(1) unsigned NOT NULL DEFAULT 0,
                  MODIFY `max_km` mediumint unsigned DEFAULT NULL,
                  MODIFY `max_time` mediumint unsigned DEFAULT NULL');
        $this->addSql('UPDATE `'.$prefix.'equipment_type` SET `max_km` = NULL WHERE `max_km`= 0');
        $this->addSql('UPDATE `'.$prefix.'equipment_type` SET `max_time` = NULL WHERE `max_time`= 0');

        //Table: type
        $this->addSql('DELETE FROM `'.$prefix.'type` WHERE `accountid` = \'-1\'');
        $this->addSql('DELETE FROM `'.$prefix.'type` WHERE `sportid` = 0');
        $this->addSql('ALTER TABLE `'.$prefix.'type`
                  MODIFY `id` int(10) unsigned auto_increment NOT NULL,
                  MODIFY `sportid` int(10) unsigned NOT NULL,
                  MODIFY `accountid` int(10) unsigned NOT NULL');

        //Table: user
        $this->addSql('DELETE FROM `'.$prefix.'user` WHERE `accountid` = \'-1\'');
        $this->addSql('UPDATE `'.$prefix.'user` SET `pulse_rest` = 0 WHERE `pulse_rest` > 255');
        $this->addSql('UPDATE `'.$prefix.'user` SET `pulse_max` = 0 WHERE `pulse_max` > 255');


        $this->addSql('ALTER TABLE `'.$prefix.'user`
                  MODIFY `id` int(10) unsigned auto_increment NOT NULL,
                  MODIFY `accountid` int(10) unsigned NOT NULL,
                  MODIFY `pulse_rest` tinyint unsigned DEFAULT NULL,
                  MODIFY `pulse_max` tinyint unsigned DEFAULT NULL,
                  MODIFY `fat` decimal(3,1) DEFAULT NULL,
                  MODIFY `water` decimal(3,1) DEFAULT NULL,
                  MODIFY `muscles` decimal(3,1) DEFAULT NULL,
                  MODIFY `weight` decimal(5,2) DEFAULT NULL,
                  MODIFY `sleep_duration` smallint(3) unsigned DEFAULT NULL');
        $this->addSql('UPDATE `'.$prefix.'user` SET `pulse_rest` = NULL WHERE `pulse_rest`= 0');
        $this->addSql('UPDATE `'.$prefix.'user` SET `pulse_max` = NULL WHERE `pulse_max`= 0');
        $this->addSql('UPDATE `'.$prefix.'user` SET `fat` = NULL WHERE `fat`= \'0.0\'');
        $this->addSql('UPDATE `'.$prefix.'user` SET `weight` = NULL WHERE `weight`= \'0.0\'');
        $this->addSql('UPDATE `'.$prefix.'user` SET `water` = NULL WHERE `water`= \'0.0\'');
        $this->addSql('UPDATE `'.$prefix.'user` SET `muscles` = NULL WHERE `muscles`= \'0.0\'');
        $this->addSql('UPDATE `'.$prefix.'user` SET `sleep_duration` = NULL WHERE `sleep_duration`= 0');

        //Table account
        $this->addSql('ALTER TABLE `'.$prefix.'account`
                  MODIFY `registerdate` int(10) unsigned DEFAULT NULL,
                  MODIFY `lastaction` int(10) unsigned DEFAULT NULL,
                  MODIFY `activation_hash` char(32) DEFAULT NULL,
                  MODIFY `deletion_hash` char(32) DEFAULT NULL,
                  MODIFY `changepw_timelimit` int(10) unsigned DEFAULT NULL,
                  MODIFY `changepw_hash` char(32) DEFAULT NULL,
                  MODIFY `allow_mails` tinyint(1) unsigned NOT NULL DEFAULT 1');
        $this->addSql('UPDATE `'.$prefix.'account` SET `registerdate` = NULL WHERE `registerdate`= 0');
        $this->addSql('UPDATE `'.$prefix.'account` SET `lastaction` = NULL WHERE `lastaction`= 0');
        $this->addSql('UPDATE `'.$prefix.'account` SET `activation_hash` = NULL WHERE `activation_hash`= \'\'');
        $this->addSql('UPDATE `'.$prefix.'account` SET `deletion_hash` = NULL WHERE `deletion_hash`= \'\'');
        $this->addSql('UPDATE `'.$prefix.'account` SET `changepw_timelimit` = NULL WHERE `changepw_timelimit`= 0');
        $this->addSql('UPDATE `'.$prefix.'account` SET `changepw_hash` = NULL WHERE `changepw_hash`= \'\'');

        $this->addSql('UPDATE `'.$prefix.'sport` SET `HFavg` = 120 WHERE `HFavg` >= 255');

        //Table sport
        $this->addSql('ALTER TABLE `'.$prefix.'sport`
                  MODIFY `accountid` int(10) unsigned NOT NULL,
                  MODIFY `short` tinyint(1) unsigned  NOT NULL DEFAULT 0,
                  MODIFY `kcal` smallint(4) unsigned NOT NULL DEFAULT 0,
                  MODIFY `HFavg` tinyint unsigned NOT NULL DEFAULT 120,
                  MODIFY `distances` tinyint(1) unsigned NOT NULL DEFAULT 1,
                  MODIFY `power` tinyint(1) unsigned NOT NULL DEFAULT 0,
                  MODIFY `outside` tinyint(1) unsigned NOT NULL DEFAULT 0
                  ');

        //Table account
        $this->addSql('ALTER TABLE `'.$prefix.'training` 
                        MODIFY `elapsed_time` int(6) DEFAULT NULL,
                        MODIFY `elevation` int(5) DEFAULT NULL,
                        MODIFY `kcal` int(5) DEFAULT NULL,
                        MODIFY `pulse_avg` int(3) DEFAULT NULL,
                        MODIFY `pulse_max` int(3) DEFAULT NULL,
                        MODIFY `trimp` int(4) DEFAULT NULL,
                        MODIFY `cadence` int(3) DEFAULT NULL,
                        MODIFY `power` int(4) DEFAULT NULL');

        $this->addSql('UPDATE `'.$prefix.'training` SET `elapsed_time` = NULL WHERE `elapsed_time` > 16777215');
        $this->addSql('UPDATE `'.$prefix.'training` SET `elapsed_time` = NULL WHERE `elapsed_time` < 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `elevation` = NULL WHERE `elevation` > 65535');
        $this->addSql('UPDATE `'.$prefix.'training` SET `elevation` = NULL WHERE `elevation` < 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `kcal` = NULL WHERE `kcal` > 65535');
        $this->addSql('UPDATE `'.$prefix.'training` SET `kcal` = NULL WHERE `kcal` < 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `pulse_avg` = NULL WHERE `pulse_avg` > 255');
        $this->addSql('UPDATE `'.$prefix.'training` SET `pulse_max` = NULL WHERE `pulse_max` > 255');
        $this->addSql('UPDATE `'.$prefix.'training` SET `trimp` = NULL WHERE `trimp` > 65535');
        $this->addSql('UPDATE `'.$prefix.'training` SET `trimp` = 0 WHERE `trimp` < 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `cadence` = NULL WHERE `cadence` > 255');
        $this->addSql('UPDATE `'.$prefix.'training` SET `power` = NULL WHERE `power` > 65535');
        $this->addSql('UPDATE `'.$prefix.'training` SET `power` = NULL WHERE `power` < 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `jd_intensity` = 0 WHERE `jd_intensity` < 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `distance` = 0 WHERE `distance` < 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `activity_id` = NULL WHERE `activity_id` < 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `fit_training_effect` = NULL WHERE `fit_training_effect` = 0');

        $this->addSql('DELETE FROM `'.$prefix.'training` WHERE `s` < 0');


        $this->addSql('ALTER TABLE `'.$prefix.'training`
                  MODIFY `sportid` int(10) unsigned NOT NULL,
                  MODIFY `typeid` int(10) unsigned DEFAULT NULL,
                  MODIFY `time` int(11) NOT NULL,  
                  MODIFY `created` int(11) unsigned DEFAULT NULL,
                  MODIFY `edited` int(11) unsigned DEFAULT NULL,
                  MODIFY `is_public` tinyint(1) unsigned NOT NULL DEFAULT 0,
                  MODIFY `is_track` tinyint(1) unsigned NOT NULL DEFAULT 0,
                  MODIFY `distance` decimal(6,2) unsigned DEFAULT NULL,
                  MODIFY `s` decimal(8,2) unsigned NOT NULL,
                  MODIFY `elapsed_time` mediumint unsigned DEFAULT NULL,
                  MODIFY `elevation` smallint unsigned DEFAULT NULL,
                  MODIFY `kcal` smallint unsigned DEFAULT NULL,
                  MODIFY `trimp` smallint unsigned DEFAULT NULL,
                  MODIFY `pulse_avg` tinyint unsigned DEFAULT NULL,
                  MODIFY `pulse_max` tinyint unsigned DEFAULT NULL,
                  MODIFY `vdot` decimal(5,2) unsigned DEFAULT NULL,
                  MODIFY `vdot_by_time` decimal(5,2) unsigned DEFAULT NULL,
                  MODIFY `vdot_with_elevation` decimal(5,2) unsigned DEFAULT NULL,
                  MODIFY `use_vdot` tinyint(1) unsigned NOT NULL DEFAULT 1,
                  MODIFY `fit_vdot_estimate` decimal(4,2) unsigned DEFAULT  NULL,
                  MODIFY `fit_recovery_time` smallint(5) unsigned DEFAULT NULL,
                  MODIFY `fit_hrv_analysis` smallint(5) unsigned DEFAULT NULL,
                  MODIFY `jd_intensity` smallint(4) unsigned DEFAULT NULL,
                  MODIFY `cadence` int(3) unsigned DEFAULT NULL,
                  MODIFY `power` int(4) unsigned DEFAULT NULL,
                  MODIFY `total_strokes` smallint(5) unsigned DEFAULT NULL,
                  MODIFY `swolf` tinyint(3) unsigned DEFAULT NULL,
                  MODIFY `stride_length` tinyint(3) unsigned DEFAULT NULL,
                  MODIFY `groundcontact` smallint(5) unsigned DEFAULT NULL,
                  MODIFY `groundcontact_balance` SMALLINT UNSIGNED DEFAULT NULL,
                  MODIFY `vertical_oscillation` tinyint(3) unsigned DEFAULT NULL,
                  MODIFY `vertical_ratio` SMALLINT UNSIGNED DEFAULT NULL,
                  MODIFY `weatherid` smallint(6) unsigned NOT NULL DEFAULT 1,
                  MODIFY `accountid` int(10) unsigned NOT NULL,
                  MODIFY `activity_id` int(10) unsigned DEFAULT NULL,
                  ADD `lock` tinyint(1) unsigned NOT NULL DEFAULT 0
                  ');
        $this->addSql('DELETE FROM `'.$prefix.'training` WHERE `sportid` = 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `typeid` = NULL WHERE `typeid`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `created` = NULL WHERE `created`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `edited` = NULL WHERE `edited`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `pulse_avg` = NULL WHERE `pulse_avg`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `pulse_max` = NULL WHERE `pulse_max`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `vdot` = NULL WHERE `vdot`= \'0.00\'');
        $this->addSql('UPDATE `'.$prefix.'training` SET `vdot_by_time` = NULL WHERE `vdot_by_time`= \'0.00\'');
        $this->addSql('UPDATE `'.$prefix.'training` SET `vdot_with_elevation` = NULL WHERE `vdot_with_elevation`= \'0.00\'');
        $this->addSql('UPDATE `'.$prefix.'training` SET `fit_vdot_estimate` = NULL WHERE `fit_vdot_estimate`= \'0.0\'');
        $this->addSql('UPDATE `'.$prefix.'training` SET `fit_recovery_time` = NULL WHERE `fit_recovery_time`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `fit_hrv_analysis` = NULL WHERE `fit_hrv_analysis`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `jd_intensity` = NULL WHERE `jd_intensity`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `cadence` = NULL WHERE `cadence`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `power` = NULL WHERE `power`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `total_strokes` = NULL WHERE `total_strokes`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `swolf` = NULL WHERE `swolf`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `total_strokes` = NULL WHERE `total_strokes`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `swolf` = NULL WHERE `swolf`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `stride_length` = NULL WHERE `stride_length`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `groundcontact` = NULL WHERE `groundcontact`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `groundcontact_balance` = NULL WHERE `groundcontact_balance`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `vertical_oscillation` = NULL WHERE `vertical_oscillation`= 0');
        $this->addSql('UPDATE `'.$prefix.'training` SET `vertical_ratio` = NULL WHERE `vertical_ratio`= 0');

        // Cleanup up weathercache
        $this->addSql('DELETE FROM `'.$prefix.'weathercache` WHERE coalesce(temperature, wind_speed, wind_deg, humidity, pressure) IS NULL OR time=0');
        $this->addSql('ALTER TABLE `'.$prefix.'weathercache`
                  MODIFY `time` int(11) NOT NULL
                  ');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {

    }
}
