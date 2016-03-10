/* 26.01.2016 - adjust v02max estimate from fit files */
ALTER TABLE `runalyze_training` CHANGE `fit_vdot_estimate` `fit_vdot_estimate` DECIMAL(4,2) UNSIGNED NOT NULL DEFAULT '0.0';

/* 27.01.2016 - add weather source */
ALTER TABLE `runalyze_training` ADD `weather_source` TINYINT(2) UNSIGNED NULL DEFAULT NULL AFTER `weatherid`;

/* 27.01.2016 - Move edit button to dataset */
INSERT INTO `runalyze_dataset` (`keyid`, `active`, `position`, `accountid`) SELECT 42, IF(`value` = 'true',1,0), 0, `accountid` FROM `runalyze_conf` WHERE `key` = 'DB_SHOW_DIRECT_EDIT_LINK';
INSERT INTO `runalyze_dataset` (`keyid`, `active`, `position`, `accountid`) SELECT 42, 1, 0, `a`.`id` FROM `runalyze_account` `a` LEFT JOIN `runalyze_dataset` `d` ON `a`.`id` = `d`.`accountid` AND `d`.`keyid` != 42 GROUP BY `d`.`accountid`;
DELETE FROM `runalyze_conf` WHERE `key` = 'DB_SHOW_DIRECT_EDIT_LINK';

/* 10.03.2016 - fix conversion of wind speed */
UPDATE `runalyze_training` SET wind_speed=2.25*wind_speed WHERE wind_speed IS NOT NULL AND wind_deg IS NOT NULL AND humidity IS NOT NULL AND pressure IS NOT NULL AND weatherid IS NOT NULL AND temperature IS NOT NULL;