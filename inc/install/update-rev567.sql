ALTER TABLE  `runalyze_training` ADD  `elevation_calculated` INT( 5 ) NOT NULL AFTER  `elevation`;
ALTER TABLE  `runalyze_training` ADD  `arr_alt_original` LONGTEXT NULL DEFAULT NULL AFTER  `arr_alt`;