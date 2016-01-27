/* 20.12.2015 */
ALTER TABLE `runalyze_account` ADD `timezone` SMALLINT unsigned NOT NULL DEFAULT '0' AFTER `language`;
/* 26.01.2016 */
ALTER TABLE `runalyze_training` ADD `timezone` SMALLINT signed NOT NULL DEFAULT '0' AFTER `time`;
