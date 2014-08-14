ALTER TABLE  `runalyze_conf` CHANGE  `value`  `value` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL;

ALTER TABLE  `runalyze_training` CHANGE  `temperature`  `temperature` TINYINT NULL DEFAULT NULL;