/* 12.01.2016 - use activity_id to detect duplicates */
UPDATE `runalyze_training` SET `activity_id` = UNIX_TIMESTAMP(`activity_id`);
ALTER table `runalyze_training` CHANGE COLUMN `activity_id` `activity_id` int(11) DEFAULT NULL;
UPDATE `runalyze_training` SET `activity_id` = `time` WHERE `activity_id` IS NULL;

/* 14.01.2016 - add new weather details */
ALTER TABLE `runalyze_training` ADD `wind_speed` tinyint(3) unsigned DEFAULT NULL AFTER `weatherid`;
ALTER TABLE `runalyze_training` ADD `wind_deg` smallint(3) unsigned DEFAULT NULL AFTER `wind_speed`;
ALTER TABLE `runalyze_training` ADD `humidity` tinyint(3) unsigned DEFAULT NULL AFTER `wind_deg`;
ALTER TABLE `runalyze_training` ADD `pressure` smallint(4) unsigned DEFAULT NULL AFTER `humidity`;
ALTER TABLE `runalyze_training` ADD `is_night` tinyint(1) unsigned DEFAULT NULL AFTER `pressure`;
