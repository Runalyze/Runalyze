ALTER TABLE `runalyze_shoe` DROP `brand`;

ALTER TABLE  `runalyze_sport` CHANGE  `kmh`  `speed` VARCHAR( 10 ) NOT NULL DEFAULT  'min/km';

UPDATE `runalyze_sport` SET `speed`="min/km" WHERE `speed`="0";
UPDATE `runalyze_sport` SET `speed`="" WHERE `distances`="0";
UPDATE `runalyze_sport` SET `speed`="km/h" WHERE `speed`="1";
UPDATE `runalyze_sport` SET `speed`="min/100m" WHERE `name`="Schwimmen";