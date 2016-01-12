/* 12.01.2016 - add new weather details */
ALTER TABLE `runalyze_training` ADD `wind_speed` tinyint(3) unsigned DEFAULT NULL AFTER `weatherid`;
ALTER TABLE `runalyze_training` ADD `wind_deg` smallint(3) unsigned DEFAULT NULL AFTER `wind_speed`;
ALTER TABLE `runalyze_training` ADD `humidity` tinyint(3) unsigned DEFAULT NULL AFTER `wind_deg`;
ALTER TABLE `runalyze_training` ADD `pressure` smallint(4) unsigned DEFAULT NULL AFTER `humidity`;
ALTER TABLE `runalyze_training` ADD `is_night` tinyint(1) unsigned NOT NULL DEFAULT '0' AFTER `pressure`;
