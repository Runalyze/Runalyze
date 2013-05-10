ALTER TABLE  `runalyze_training` ADD  `arr_temperature` LONGTEXT NULL DEFAULT NULL AFTER  `arr_pace`;
ALTER TABLE  `runalyze_training` ADD  `arr_power` LONGTEXT NULL DEFAULT NULL AFTER  `arr_pace`;
ALTER TABLE  `runalyze_training` ADD  `arr_cadence` LONGTEXT NULL DEFAULT NULL AFTER  `arr_pace`;

ALTER TABLE  `runalyze_training` ADD  `power` INT( 4 ) NOT NULL DEFAULT  '0' AFTER  `trimp`;
ALTER TABLE  `runalyze_training` ADD  `cadence` INT( 3 ) NOT NULL DEFAULT  '0' AFTER  `trimp`;

ALTER TABLE  `runalyze_sport` ADD  `power` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `pulse`;
UPDATE `runalyze_sport` SET `power`=1 WHERE `name`="Radfahren";

INSERT INTO `runalyze_dataset` (`name`, `label`, `description`, `distance`, `outside`, `pulse`, `type`, `modus`, `class`, `style`, `position`, `summary`, `summary_mode`, `accountid`) SELECT 'cadence', 'Schrittfrequenz', 'Anzeige der durchschnittlichen Schrittfrequenz.', 1, 0, 0, 0, 1, 'small', '', 19, 1, 'AVG', `id` FROM `runalyze_account`;
INSERT INTO `runalyze_dataset` (`name`, `label`, `description`, `distance`, `outside`, `pulse`, `type`, `modus`, `class`, `style`, `position`, `summary`, `summary_mode`, `accountid`) SELECT 'power', 'Power', 'Anzeige der berechneten virtuellen Power.', 1, 1, 0, 0, 1, 'small', '', 20, 1, 'SUM', `id` FROM `runalyze_account`;
