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
