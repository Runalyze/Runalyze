/* 18.10.2015 - drop 'types' flag for sport */
ALTER TABLE `runalyze_sport` DROP `types`;

/* 11.11.2015 - refactor dataset */
ALTER TABLE `runalyze_dataset` ADD `keyid` TINYINT UNSIGNED NOT NULL FIRST;
UPDATE `runalyze_dataset` SET `keyid`=1 WHERE `name`="sportid";
UPDATE `runalyze_dataset` SET `keyid`=2 WHERE `name`="typeid";
UPDATE `runalyze_dataset` SET `keyid`=3 WHERE `name`="time";
UPDATE `runalyze_dataset` SET `keyid`=5 WHERE `name`="distance";
UPDATE `runalyze_dataset` SET `keyid`=6 WHERE `name`="s";
UPDATE `runalyze_dataset` SET `keyid`=7 WHERE `name`="pace";
UPDATE `runalyze_dataset` SET `keyid`=9 WHERE `name`="elevation";
UPDATE `runalyze_dataset` SET `keyid`=10 WHERE `name`="kcal";
UPDATE `runalyze_dataset` SET `keyid`=11 WHERE `name`="pulse_avg";
UPDATE `runalyze_dataset` SET `keyid`=12 WHERE `name`="pulse_max";
UPDATE `runalyze_dataset` SET `keyid`=19 WHERE `name`="trimp";
UPDATE `runalyze_dataset` SET `keyid`=26 WHERE `name`="temperature";
UPDATE `runalyze_dataset` SET `keyid`=27 WHERE `name`="weatherid";
UPDATE `runalyze_dataset` SET `keyid`=28 WHERE `name`="routeid";
UPDATE `runalyze_dataset` SET `keyid`=29 WHERE `name`="splits";
UPDATE `runalyze_dataset` SET `keyid`=30 WHERE `name`="comment";
UPDATE `runalyze_dataset` SET `keyid`=13 WHERE `name`="vdoticon";
UPDATE `runalyze_dataset` SET `keyid`=31 WHERE `name`="partner";
UPDATE `runalyze_dataset` SET `keyid`=20 WHERE `name`="cadence";
UPDATE `runalyze_dataset` SET `keyid`=21 WHERE `name`="power";
UPDATE `runalyze_dataset` SET `keyid`=18 WHERE `name`="jd_intensity";
UPDATE `runalyze_dataset` SET `keyid`=24 WHERE `name`="groundcontact";
UPDATE `runalyze_dataset` SET `keyid`=25 WHERE `name`="vertical_oscillation";
UPDATE `runalyze_dataset` SET `keyid`=14 WHERE `name`="vdot";
UPDATE `runalyze_dataset` SET `keyid`=23 WHERE `name`="stride_length";
UPDATE `runalyze_dataset` SET `keyid`=15 WHERE `name`="fit_vdot_estimate";
UPDATE `runalyze_dataset` SET `keyid`=16 WHERE `name`="fit_recovery_time";
UPDATE `runalyze_dataset` SET `keyid`=17 WHERE `name`="fit_hrv_analysis";
DELETE FROM `runalyze_dataset` WHERE `keyid`=0;

UPDATE `runalyze_dataset` SET `active`=0 WHERE `modus`=1;

ALTER TABLE `runalyze_dataset` DROP `id`;
ALTER TABLE `runalyze_dataset` ADD PRIMARY KEY(`accountid`, `keyid`);
ALTER TABLE `runalyze_dataset` DROP INDEX `accountid`;
ALTER TABLE `runalyze_dataset` ADD INDEX `position` (`accountid`, `position`);
ALTER TABLE `runalyze_dataset` DROP `name`, DROP `modus`, DROP `class`, DROP `summary`, DROP `summary_mode`;
ALTER TABLE `runalyze_dataset` CHANGE `active` `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1';
ALTER TABLE `runalyze_dataset` CHANGE `position` `position` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `runalyze_dataset` CHANGE `accountid` `accountid` INT(10) UNSIGNED NOT NULL;

/* 21.11.2015 - add groundcontact_balance and arr_vertical_ratio to trackdata */
ALTER TABLE `runalyze_trackdata` ADD `groundcontact_balance` LONGTEXT NULL DEFAULT NULL AFTER `groundcontact`;
ALTER TABLE `runalyze_training` ADD  `vertical_ratio` SMALLINT UNSIGNED NOT NULL DEFAULT  '0' AFTER `vertical_oscillation`;
ALTER TABLE `runalyze_training` ADD  `groundcontact_balance` SMALLINT UNSIGNED NOT NULL DEFAULT  '0' AFTER  `groundcontact`;

/* 26.11.2015 - calculate vertical ratio for existing activities */
UPDATE `runalyze_training` SET `vertical_ratio` = 100 * `vertical_oscillation` / `stride_length` WHERE `stride_length` > 0;

/* 28.11.2015 -  add tables for tags */
CREATE TABLE IF NOT EXISTS `runalyze_tag` (
  `id` int(10) unsigned NOT NULL,
  `tag` varchar(50) NOT NULL,
  `accountid` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `runalyze_activity_tag` (
  `activityid` int(10) unsigned NOT NULL,
  `tagid` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `runalyze_tag` ADD PRIMARY KEY (`id`), ADD KEY `accountid` (`accountid`);
ALTER TABLE `runalyze_tag` MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `runalyze_activity_tag` ADD PRIMARY KEY (`activityid`,`tagid`), ADD KEY `tagid` (`tagid`);

INSERT INTO `runalyze_tag` (`accountid`, `tag`) SELECT `id`, 'Running drills' FROM `runalyze_account` WHERE `language` = 'en' OR `language` = '';
INSERT INTO `runalyze_tag` (`accountid`, `tag`) SELECT `id`, 'Lauf-ABC' FROM `runalyze_account` WHERE `language` = 'de';
INSERT INTO `runalyze_tag` (`accountid`, `tag`) SELECT `id`, 'tècnica' FROM `runalyze_account` WHERE `language` = 'ca';
INSERT INTO `runalyze_tag` (`accountid`, `tag`) SELECT `id`, 'Loop ABC' FROM `runalyze_account` WHERE `language` = 'nl';
INSERT INTO `runalyze_tag` (`accountid`, `tag`) SELECT `id`, 'Technice' FROM `runalyze_account` WHERE `language` = 'it';
INSERT INTO `runalyze_tag` (`accountid`, `tag`) SELECT `id`, 'Ćwiczenia biegowe' FROM `runalyze_account` WHERE `language` = 'pl';

INSERT INTO `runalyze_activity_tag` (`activityid`, `tagid`) SELECT tr.id, tg.id FROM `runalyze_training` tr LEFT JOIN `runalyze_tag` tg ON tr.accountid=tg.accountid where `abc` = 1 AND `tg`.`id` IS NOT NULL;
ALTER TABLE `runalyze_training` DROP `abc`;

UPDATE `runalyze_plugin` SET `key`="RunalyzePluginStat_Tag" WHERE `key`="RunalyzePluginStat_Laufabc";

/* 29.11.2015 - add equipment to dataset */
ALTER TABLE `runalyze_sport` ADD `main_equipmenttypeid` int(10) unsigned NOT NULL DEFAULT '0' AFTER `outside`;

/* 29.11.2015 - geohashes for _route table */
ALTER TABLE `runalyze_route` ADD `geohashes` longtext AFTER `lngs`, ADD `startpoint` char(10) AFTER `startpoint_lng`, ADD `endpoint` char(10) AFTER `endpoint_lng`,  ADD `min` char(10) AFTER `min_lng`, ADD `max` char(10) AFTER `max_lng`;

/* 09.12.2015 - clean activities without sportid */
UPDATE `runalyze_training` AS `tr` INNER JOIN `runalyze_conf` AS `conf` ON `conf`.`accountid` = `tr`.`accountid` AND `conf`.`key` = 'MAINSPORT' SET `tr`.`sportid` = `conf`.`value` WHERE `sportid` = 0;

/* Constraints at the bottom as they may cause errors */
ALTER TABLE `runalyze_activity_tag` ADD CONSTRAINT `runalyze_activity_tag_ibfk_1` FOREIGN KEY (`tagid`) REFERENCES `runalyze_tag` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `runalyze_activity_tag` ADD CONSTRAINT `runalyze_activity_tag_ibfk_2` FOREIGN KEY (`activityid`) REFERENCES `runalyze_training` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `runalyze_tag` ADD CONSTRAINT `runalyze_tag_ibfk_1` FOREIGN KEY (`accountid`) REFERENCES `runalyze_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
