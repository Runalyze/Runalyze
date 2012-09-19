ALTER TABLE  `runalyze_conf` DROP  `category`;
ALTER TABLE  `runalyze_training` ADD  `jd_intensity` SMALLINT( 4 ) NOT NULL DEFAULT  '0' AFTER  `vdot`;
ALTER TABLE  `runalyze_dataset` ADD  `active` BOOL NOT NULL DEFAULT  '1' AFTER  `name`;
ALTER TABLE  `runalyze_training` ADD  `no_vdot` BOOL NOT NULL DEFAULT  '0' AFTER  `vdot`;
ALTER TABLE  `runalyze_training` ADD  `created` INT NOT NULL AFTER  `time`, ADD  `edited` INT NOT NULL AFTER  `created`;
ALTER TABLE  `runalyze_training` ADD  `creator` VARCHAR( 100 ) NOT NULL , ADD  `creator_details` TINYTEXT NOT NULL , ADD  `elevation_corrected` BOOL NOT NULL DEFAULT  '0';