/* Rev409 */
ALTER TABLE  `runalyze_training` ADD  `vdot_by_time` DECIMAL( 5, 2 ) NOT NULL DEFAULT  '0' AFTER  `vdot`;

/* Rev435 */
ALTER TABLE  `runalyze_type` ADD  `sportid` INT( 11 ) NOT NULL DEFAULT  '0' AFTER  `splits`;
UPDATE `runalyze_type` AS TY LEFT JOIN runalyze_conf AS CO ON TY.accountid = CO.accountid SET TY.sportid = CO.value WHERE CO.key LIKE  'RUNNINGSPORT';

/* Rev 437 */
ALTER TABLE `runalyze_shoe` DROP `brand`;

ALTER TABLE  `runalyze_sport` CHANGE  `kmh`  `speed` VARCHAR( 10 ) NOT NULL DEFAULT  'min/km';

UPDATE `runalyze_sport` SET `speed`="min/km" WHERE `speed`="0";
UPDATE `runalyze_sport` SET `speed`="" WHERE `distances`="0";
UPDATE `runalyze_sport` SET `speed`="km/h" WHERE `speed`="1";
UPDATE `runalyze_sport` SET `speed`="min/100m" WHERE `name`="Schwimmen";

/* Rev456 */
DELETE FROM `runalyze_conf` WHERE `key`="JS_USE_TOOLTIP";
DELETE FROM `runalyze_conf` WHERE `key`="DESIGN_TOOLBAR_POSITION";
DELETE FROM `runalyze_conf` WHERE `key`="PLUGIN_SHOW_MOVE_LINK";
DELETE FROM `runalyze_conf` WHERE `key`="PLUGIN_SHOW_CONFIG_LINK";
DELETE FROM `runalyze_conf` WHERE `key`="TRAINING_PLOTS_BELOW";