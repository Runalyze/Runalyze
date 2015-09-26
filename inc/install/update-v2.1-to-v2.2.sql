/* ATTENTION: Not everything is ordered by date, as all constraint definitions have to stay at the bottom */
/* 08.07.2015 - add hrv table */
CREATE TABLE IF NOT EXISTS `runalyze_hrv` ( `accountid` int(10) unsigned NOT NULL, `activityid` int(10) unsigned NOT NULL, `data` longtext ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `runalyze_hrv` ADD PRIMARY KEY (`activityid`), ADD KEY `accountid` (`accountid`);

/* 16.07.2015 - on branch feature/removePaceArray - remove pace from db */
ALTER TABLE `runalyze_trackdata` DROP `pace`;

/* 01.08.2015 - add swim table */

CREATE TABLE IF NOT EXISTS `runalyze_swimdata` (
  `accountid` int(10) unsigned NOT NULL,
  `activityid` int(10) unsigned NOT NULL,
  `stroke` longtext,
  `stroketype` longtext,
  `pool_length` smallint(5) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `runalyze_swimdata`
 ADD PRIMARY KEY (`activityid`), ADD KEY `accountid` (`accountid`);

ALTER TABLE `runalyze_training` ADD `total_strokes` smallint(5) unsigned NOT NULL DEFAULT '0', ADD `swolf` tinyint(3) unsigned NOT NULL DEFAULT '0' AFTER `power`;

/* 04.09.2015 - add recovery advisor for fit files */
ALTER TABLE `runalyze_training` ADD `fit_vdot_estimate` TINYINT(2) UNSIGNED NOT NULL DEFAULT '0' AFTER `use_vdot`, ADD `fit_recovery_time` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `fit_vdot_estimate`, ADD `fit_hrv_analysis` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `fit_recovery_time`;

/* 16.09.2015 - add short mode for types */
ALTER TABLE `runalyze_type` ADD `short` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `sportid`;

/* 17.09.2015 - add statistics from fit files to dataset */
INSERT INTO `runalyze_dataset` (`name`, `active`, `modus`, `class`, `style`, `position`, `summary`, `summary_mode`, `accountid`) SELECT 'fit_vdot_estimate', 1, 1, 'small', '', 29, 1, 'AVG', `id` FROM `runalyze_account`;
INSERT INTO `runalyze_dataset` (`name`, `active`, `modus`, `class`, `style`, `position`, `summary`, `summary_mode`, `accountid`) SELECT 'fit_recovery_time', 1, 1, 'small', '', 30, 0, 'NO', `id` FROM `runalyze_account`;
INSERT INTO `runalyze_dataset` (`name`, `active`, `modus`, `class`, `style`, `position`, `summary`, `summary_mode`, `accountid`) SELECT 'fit_hrv_analysis', 1, 1, 'small', '', 31, 1, 'AVG', `id` FROM `runalyze_account`;

/* 19.09.2015 - add equipment for all sport types */
ALTER TABLE `runalyze_training` CHANGE `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `runalyze_sport` CHANGE `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `runalyze_account` CHANGE `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

UPDATE `runalyze_plugin` SET `key`="RunalyzePluginPanel_Equipment" WHERE `key`="RunalyzePluginPanel_Schuhe";
DELETE FROM `runalyze_plugin_conf` WHERE `config` = 'for_clothes' OR `config` = 'for_weather';
DELETE FROM `runalyze_dataset` WHERE `name` = 'shoeid' OR `name` = 'clothes';

CREATE TABLE IF NOT EXISTS `runalyze_equipment_type` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `input` tinyint(1) NOT NULL DEFAULT '0',
  `max_km` int(11) NOT NULL DEFAULT '0',
  `max_time` int(11) NOT NULL DEFAULT '0',
  `accountid` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `runalyze_equipment` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `typeid` int(10) unsigned NOT NULL DEFAULT '0',
  `notes` tinytext NOT NULL,
  `distance` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `additional_km` int(10) unsigned NOT NULL DEFAULT '0',
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `accountid` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `runalyze_equipment_sport` (
  `sportid` int(10) unsigned NOT NULL,
  `equipment_typeid` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `runalyze_activity_equipment` (
  `activityid` int(10) unsigned NOT NULL,
  `equipmentid` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `runalyze_equipment` ADD PRIMARY KEY (`id`), ADD KEY `accountid` (`accountid`), ADD KEY `typeid` (`typeid`);
ALTER TABLE `runalyze_equipment_type` ADD PRIMARY KEY (`id`), ADD KEY `accountid` (`accountid`);
ALTER TABLE `runalyze_activity_equipment` ADD PRIMARY KEY (`activityid`,`equipmentid`), ADD KEY `equipmentid` (`equipmentid`);
ALTER TABLE `runalyze_equipment_sport` ADD PRIMARY KEY (`sportid`,`equipment_typeid`), ADD KEY `equipment_typeid` (`equipment_typeid`);
ALTER TABLE `runalyze_equipment` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `runalyze_equipment_type` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

DROP TRIGGER IF EXISTS `del_tr_train`;
DELIMITER //
CREATE TRIGGER `del_tr_train` AFTER DELETE ON `runalyze_account`
 FOR EACH ROW BEGIN
		DELETE FROM runalyze_conf WHERE accountid = OLD.id;
		DELETE FROM runalyze_dataset WHERE accountid = OLD.id;
		DELETE FROM runalyze_plugin WHERE accountid = OLD.id;
		DELETE FROM runalyze_sport WHERE accountid = OLD.id;
		DELETE FROM runalyze_training WHERE accountid = OLD.id;
		DELETE FROM runalyze_type WHERE accountid = OLD.id;
		DELETE FROM runalyze_user WHERE accountid = OLD.id;
	END
//
DELIMITER ;

/* 26.09.2015 - add further constraints */
ALTER TABLE `runalyze_plugin` CHANGE `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

/* CONSTRAINTS - new order! */
/* 19.09.2015 - add equipment for all sport types */
ALTER TABLE `runalyze_activity_equipment`
	ADD CONSTRAINT `runalyze_activity_equipment_ibfk_1` FOREIGN KEY (`equipmentid`) REFERENCES `runalyze_equipment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	ADD CONSTRAINT `runalyze_activity_equipment_ibfk_2` FOREIGN KEY (`activityid`) REFERENCES `runalyze_training` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `runalyze_equipment`
	ADD CONSTRAINT `runalyze_equipment_ibfk_1` FOREIGN KEY (`typeid`) REFERENCES `runalyze_equipment_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	ADD CONSTRAINT `runalyze_equipment_ibfk_2` FOREIGN KEY (`accountid`) REFERENCES `runalyze_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `runalyze_equipment_sport`
	ADD CONSTRAINT `runalyze_equipment_sport_ibfk_1` FOREIGN KEY (`sportid`) REFERENCES `runalyze_sport` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	ADD CONSTRAINT `runalyze_equipment_sport_ibfk_2` FOREIGN KEY (`equipment_typeid`) REFERENCES `runalyze_equipment_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `runalyze_equipment_type` ADD CONSTRAINT `runalyze_equipment_type_ibfk_1` FOREIGN KEY (`accountid`) REFERENCES `runalyze_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/* 26.09.2015 - add further constraints */
ALTER TABLE `runalyze_hrv` ADD FOREIGN KEY (`accountid`) REFERENCES `runalyze`.`runalyze_account`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `runalyze_hrv` ADD FOREIGN KEY (`activityid`) REFERENCES `runalyze`.`runalyze_training`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `runalyze_plugin_conf` ADD FOREIGN KEY (`pluginid`) REFERENCES `runalyze`.`runalyze_plugin`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `runalyze_route` ADD FOREIGN KEY (`accountid`) REFERENCES `runalyze`.`runalyze_account`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `runalyze_swimdata` ADD FOREIGN KEY (`accountid`) REFERENCES `runalyze`.`runalyze_account`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `runalyze_swimdata` ADD FOREIGN KEY (`activityid`) REFERENCES `runalyze`.`runalyze_training`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `runalyze_trackdata` ADD FOREIGN KEY (`accountid`) REFERENCES `runalyze`.`runalyze_account`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `runalyze_trackdata` ADD FOREIGN KEY (`activityid`) REFERENCES `runalyze`.`runalyze_training`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
