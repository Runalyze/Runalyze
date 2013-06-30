/* Rev409 */
ALTER TABLE  `runalyze_training` ADD  `vdot_by_time` DECIMAL( 5, 2 ) NOT NULL DEFAULT  '0' AFTER  `vdot`;

/* Rev435 */
ALTER TABLE  `runalyze_type` ADD  `sportid` INT( 11 ) NOT NULL DEFAULT  '0' AFTER  `splits`;
UPDATE `runalyze_type` AS TY LEFT JOIN runalyze_conf AS CO ON TY.accountid = CO.accountid SET TY.sportid = CO.value WHERE CO.key = 'RUNNINGSPORT';

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

/* Rev458 */
INSERT INTO `runalyze_type` (`name`, `abbr`, `RPE`, `splits`, `sportid`, `accountid`)
SELECT DISTINCT TY.name, TY.abbr, TY.RPE, TY.splits, TR.sportid, TR.accountid FROM `runalyze_training` AS TR 
INNER JOIN `runalyze_type` AS TY ON TR.typeid = TY.id 
WHERE TR.sportid IN (SELECT id FROM `runalyze_sport` WHERE `name` != 'Laufen' AND types = 1) AND TR.typeid != 0;
UPDATE `runalyze_training` AS TR
INNER JOIN `runalyze_type` AS TY ON TR.sportid = TY.sportid AND TR.accountid = TY.accountid
SET TR.typeid = TY.id 
WHERE TR.sportid IN (SELECT id FROM `runalyze_sport` WHERE `name` != 'Laufen' AND types = 1) AND TR.typeid != 0;

/* Rev490 */
ALTER TABLE  `runalyze_sport` DROP  `online`;

/* Rev496 */
ALTER TABLE  `runalyze_type` DROP  `splits`;

/* Rev520 */
ALTER TABLE  `runalyze_training` ADD  `arr_temperature` LONGTEXT NULL DEFAULT NULL AFTER  `arr_pace`;
ALTER TABLE  `runalyze_training` ADD  `arr_power` LONGTEXT NULL DEFAULT NULL AFTER  `arr_pace`;
ALTER TABLE  `runalyze_training` ADD  `arr_cadence` LONGTEXT NULL DEFAULT NULL AFTER  `arr_pace`;

ALTER TABLE  `runalyze_training` ADD  `power` INT( 4 ) NOT NULL DEFAULT  '0' AFTER  `trimp`;
ALTER TABLE  `runalyze_training` ADD  `cadence` INT( 3 ) NOT NULL DEFAULT  '0' AFTER  `trimp`;

ALTER TABLE  `runalyze_sport` ADD  `power` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `pulse`;
UPDATE `runalyze_sport` SET `power`=1 WHERE `name`="Radfahren";

INSERT INTO `runalyze_dataset` (`name`, `label`, `description`, `distance`, `outside`, `pulse`, `type`, `modus`, `class`, `style`, `position`, `summary`, `summary_mode`, `accountid`) SELECT 'cadence', 'Trittfrequenz', 'Anzeige der durchschnittlichen Schritt- oder Trittfrequenz.', 1, 0, 0, 0, 1, 'small', '', 19, 1, 'AVG', `id` FROM `runalyze_account`;
INSERT INTO `runalyze_dataset` (`name`, `label`, `description`, `distance`, `outside`, `pulse`, `type`, `modus`, `class`, `style`, `position`, `summary`, `summary_mode`, `accountid`) SELECT 'power', 'Power', 'Anzeige der berechneten virtuellen Power.', 1, 1, 0, 0, 1, 'small', '', 20, 1, 'SUM', `id` FROM `runalyze_account`;

/* Rev538 */
ALTER TABLE  `runalyze_plugin` DROP  `filename`;

/* Rev540 */
ALTER TABLE  `runalyze_training` CHANGE  `s`  `s` DECIMAL( 8, 2 ) NOT NULL DEFAULT  '0.00';
ALTER TABLE  `runalyze_training` ADD  `elapsed_time` INT( 6 ) NOT NULL DEFAULT '0' AFTER  `s`;

/* Rev567 */
ALTER TABLE  `runalyze_training` ADD  `elevation_calculated` INT( 5 ) NOT NULL AFTER  `elevation`;
ALTER TABLE  `runalyze_training` ADD  `arr_alt_original` LONGTEXT NULL DEFAULT NULL AFTER  `arr_alt`;