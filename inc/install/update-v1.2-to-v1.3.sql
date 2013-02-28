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