/* 12.01.2016 - use activity_id to detect duplicates */
UPDATE `runalyze_training` SET `activity_id` = 0 WHERE CHAR_LENGTH(`activity_id`) > 27;
UPDATE `runalyze_training` SET `activity_id` = 60*FLOOR(UNIX_TIMESTAMP(STR_TO_DATE(TRIM(`activity_id`), '%Y-%m-%dT%H:%i:%s.%fZ'))/60) WHERE CHAR_LENGTH(`activity_id`) > 20;
UPDATE `runalyze_training` SET `activity_id` = 60*FLOOR(UNIX_TIMESTAMP(STR_TO_DATE(TRIM(`activity_id`), '%Y-%m-%dT%H:%i:%sZ'))/60) WHERE CHAR_LENGTH(`activity_id`) = 20;
UPDATE `runalyze_training` SET `activity_id` = 0 WHERE `activity_id` = '';
ALTER table `runalyze_training` CHANGE COLUMN `activity_id` `activity_id` int(11) DEFAULT NULL;
UPDATE `runalyze_training` SET `activity_id` = 60*FLOOR(`time`)/60 WHERE `activity_id` = 0 OR `activity_id` = NULL;

/* 14.01.2016 - add new weather details */
ALTER TABLE `runalyze_training`
  ADD `wind_speed` tinyint(3) unsigned DEFAULT NULL AFTER `weatherid`,
  ADD `wind_deg` smallint(3) unsigned DEFAULT NULL AFTER `wind_speed`,
  ADD `humidity` tinyint(3) unsigned DEFAULT NULL AFTER `wind_deg`,
  ADD `pressure` smallint(4) unsigned DEFAULT NULL AFTER `humidity`,
  ADD `is_night` tinyint(1) unsigned DEFAULT NULL AFTER `pressure`;
