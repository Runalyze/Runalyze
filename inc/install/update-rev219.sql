ALTER TABLE `runalyze_training` ADD `is_public` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `time`;
ALTER TABLE `runalyze_training` ADD `notes` TEXT NOT NULL AFTER `shoeid`;
ALTER TABLE `runalyze_plugin` ADD `internal_data` TEXT NOT NULL AFTER `config`;