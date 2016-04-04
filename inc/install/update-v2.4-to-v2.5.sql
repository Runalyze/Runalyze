/* 26.01.2016 - adjust v02max estimate from fit files */
ALTER TABLE `runalyze_training` CHANGE `fit_vdot_estimate` `fit_vdot_estimate` DECIMAL(4,2) UNSIGNED NOT NULL DEFAULT '0.0';

/* 27.01.2016 - add weather source */
ALTER TABLE `runalyze_training` ADD `weather_source` TINYINT(2) UNSIGNED NULL DEFAULT NULL AFTER `weatherid`;

/* 27.01.2016 - Move edit button to dataset */
INSERT INTO `runalyze_dataset` (`keyid`, `active`, `position`, `accountid`) SELECT 42, 1, 0, `accountid` FROM `runalyze_dataset` GROUP BY `accountid` HAVING count(*) >1 
DELETE FROM `runalyze_conf` WHERE `key` = 'DB_SHOW_DIRECT_EDIT_LINK';

/* 10.03.2016 - fix conversion of wind speed */
UPDATE `runalyze_training` SET wind_speed=2.25*wind_speed WHERE wind_speed IS NOT NULL AND wind_speed > 0 AND wind_speed <=102 AND wind_deg IS NOT NULL AND humidity IS NOT NULL AND pressure IS NOT NULL AND weatherid IS NOT NULL AND temperature IS NOT NULL;

/* 20.03.2016 - originally in feature/timezone on 20.12.2015 */
ALTER TABLE `runalyze_account` ADD `timezone` SMALLINT(5) unsigned NOT NULL DEFAULT '0' AFTER `language`;
/* 20.03.2016 - originally in feature/timezone on  26.01.2016 */
ALTER TABLE `runalyze_training` ADD `timezone_offset` SMALLINT(6) signed DEFAULT NULL AFTER `time`;

/* 30.03.2016 - set default timezone to Europe/Berlin */
UPDATE `runalyze_account` SET `timezone`=43;
