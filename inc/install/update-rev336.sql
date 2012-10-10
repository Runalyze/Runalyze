ALTER TABLE  `runalyze_training` DROP  `no_vdot`;
ALTER TABLE  `runalyze_training` ADD  `use_vdot` TINYINT( 1 ) NOT NULL DEFAULT  '1' AFTER  `vdot`;
UPDATE `runalyze_training` SET `use_vdot` = 1;