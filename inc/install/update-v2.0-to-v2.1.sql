/* 20.03.2015 - add vdot value to dataset */
UPDATE `runalyze_dataset` set name = 'vdoticon' WHERE name = 'vdot';
INSERT INTO `runalyze_dataset` (`name`, `active`, `modus`, `class`, `style`, `position`, `summary`, `summary_mode`, `accountid`) SELECT 'vdot', 1, 2, '', '', 27, 1, 'AVG', `id` FROM `runalyze_account`;

/* 11.04.2015 - remove pace unit 'none' */
UPDATE `runalyze_sport` SET `speed`="km/h" WHERE `speed`="";

/* 12.04.2015 - remove long run type id */
UPDATE `runalyze_conf` SET `value`="race" WHERE `key`="TRAINING_MAP_PUBLIC_MODE" AND `value`="race-longjog";
DELETE FROM `runalyze_conf` WHERE `key`="TYPE_ID_LONGRUN";

/* 12.04.2015 - update sport icons */
UPDATE runalyze_sport SET img="icons8-Sports-Mode" where `img`="icons8-sports_mode";
UPDATE runalyze_sport SET img="icons8-Running" where `img`="icons8-running";
UPDATE runalyze_sport SET img="icons8-Regular-Biking" where `img`="icons8-regular_biking";
UPDATE runalyze_sport SET img="icons8-Swimming" where `img`="icons8-swimming";
UPDATE runalyze_sport SET img="icons8-Yoga" where `img`="icons8-yoga";

/* 12.04.2015 - remove distances/pulse from sport configuration */
ALTER TABLE `runalyze_sport` DROP `pulse`;

/* 15.04.2015 - add language to account configuration */
ALTER TABLE `runalyze_account` ADD `language` VARCHAR(3) NOT NULL AFTER `mail`;

/* 25.05.2015 - use NULL for min/max/start/end values of routes */
ALTER TABLE `runalyze_route` CHANGE `startpoint_lat` `startpoint_lat` FLOAT(8,5) NULL DEFAULT NULL, CHANGE `startpoint_lng` `startpoint_lng` FLOAT(8,5) NULL DEFAULT NULL, CHANGE `endpoint_lat` `endpoint_lat` FLOAT(8,5) NULL DEFAULT NULL, CHANGE `endpoint_lng` `endpoint_lng` FLOAT(8,5) NULL DEFAULT NULL, CHANGE `min_lat` `min_lat` FLOAT(8,5) NULL DEFAULT NULL, CHANGE `min_lng` `min_lng` FLOAT(8,5) NULL DEFAULT NULL, CHANGE `max_lat` `max_lat` FLOAT(8,5) NULL DEFAULT NULL, CHANGE `max_lng` `max_lng` FLOAT(8,5) NULL DEFAULT NULL;
UPDATE `runalyze_route` SET `startpoint_lat`=NULL, `startpoint_lng`=NULL, `endpoint_lat`=NULL, `endpoint_lng`=NULL, `min_lat`=NULL, `min_lng`=NULL, `max_lat`=NULL, `max_lng`=NULL WHERE `lats`="";

/* 25.05.2015 - use default values everywhere */
ALTER TABLE `runalyze_type` CHANGE `abbr` `abbr` VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `runalyze_training` CHANGE `created` `created` INT(11) NOT NULL DEFAULT '0', CHANGE `edited` `edited` INT(11) NOT NULL DEFAULT '0', CHANGE `routeid` `routeid` INT(10) UNSIGNED NOT NULL DEFAULT '0', CHANGE `clothes` `clothes` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `creator` `creator` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `runalyze_shoe` CHANGE `weight` `weight` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `runalyze_route` CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `cities` `cities` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `distance` `distance` DECIMAL(6,2) UNSIGNED NOT NULL DEFAULT '0', CHANGE `elevation` `elevation` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0', CHANGE `elevation_up` `elevation_up` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0', CHANGE `elevation_down` `elevation_down` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0', CHANGE `elevations_source` `elevations_source` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `in_routenet` `in_routenet` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `runalyze_plugin` CHANGE `type` `type` ENUM('panel','stat','tool') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'stat', CHANGE `order` `order` SMALLINT(6) NOT NULL DEFAULT '0';
ALTER TABLE `runalyze_dataset` CHANGE `class` `class` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `style` `style` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
ALTER TABLE `runalyze_clothes` CHANGE `short` `short` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `order` `order` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `runalyze_account` CHANGE `name` `name` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `language` `language` VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `password` `password` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `salt` `salt` CHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `registerdate` `registerdate` INT(11) NOT NULL DEFAULT '0', CHANGE `lastaction` `lastaction` INT(11) NOT NULL DEFAULT '0', CHANGE `lastlogin` `lastlogin` INT(11) NOT NULL DEFAULT '0', CHANGE `autologin_hash` `autologin_hash` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `changepw_hash` `changepw_hash` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `changepw_timelimit` `changepw_timelimit` INT(11) NOT NULL DEFAULT '0', CHANGE `activation_hash` `activation_hash` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '', CHANGE `deletion_hash` `deletion_hash` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';
/* And some more null fields, as MySQL does not support default values for text fields */
ALTER TABLE `runalyze_training` CHANGE `notes` `notes` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `creator_details` `creator_details` TINYTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `runalyze_trackdata` CHANGE `time` `time` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `distance` `distance` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `pace` `pace` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `heartrate` `heartrate` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `cadence` `cadence` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `power` `power` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `temperature` `temperature` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `groundcontact` `groundcontact` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `vertical_oscillation` `vertical_oscillation` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `pauses` `pauses` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `runalyze_route` CHANGE `lats` `lats` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `lngs` `lngs` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `elevations_original` `elevations_original` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, CHANGE `elevations_corrected` `elevations_corrected` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

/* 05.06.2015 - add opt out for mails */
ALTER TABLE `runalyze_account` ADD `allow_mails` TINYINT(1) NOT NULL DEFAULT '1' AFTER `deletion_hash` ;

/* 05.06.2015 - add stride length */
ALTER TABLE `runalyze_training` ADD `stride_length` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER `power`;
INSERT INTO `runalyze_dataset` (`name`, `active`, `modus`, `class`, `style`, `position`, `summary`, `summary_mode`, `accountid`) SELECT 'stride_length', 1, 2, 'small', '', 28, 1, 'AVG', `id` FROM `runalyze_account`;

/* 07.06.2015 - fix clothes for FIND_IN_SET */
UPDATE `runalyze_training` SET `clothes` = REPLACE(`clothes`, ' ', '');

/* 07.06.2015 - don't use rpe anymore */
ALTER TABLE `runalyze_sport` DROP `RPE`;
ALTER TABLE `runalyze_type` ADD `hr_avg` TINYINT(3) UNSIGNED NOT NULL DEFAULT '100' AFTER `sportid`, ADD `quality_session` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `hr_avg`;
UPDATE `runalyze_type` SET `quality_session` = (`RPE` > 4);
UPDATE `runalyze_type` SET `hr_avg` = IF(`RPE`>8,90+10*`RPE`,(120 + 7.5 * `RPE`));
ALTER TABLE `runalyze_type` DROP `RPE`;


/* 12.06.2015 - add sleep duration and notice field to User Data */
ALTER TABLE `runalyze_user` ADD `sleep_duration` SMALLINT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER `muscles`, ADD `notes` TEXT NULL DEFAULT NULL AFTER `sleep_duration`;

/* 13.06.2015 - change empty string for array objects */
UPDATE `runalyze_trackdata` SET `pauses`="" WHERE `pauses`="[]";
