ALTER TABLE  `runalyze_training` CHANGE  `s`  `s` DECIMAL( 8, 2 ) NOT NULL DEFAULT  '0.00';
ALTER TABLE  `runalyze_training` ADD  `elapsed_time` INT( 6 ) NOT NULL DEFAULT '0' AFTER  `s`;