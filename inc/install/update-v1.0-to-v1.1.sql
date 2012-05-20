/* Rev140 */
ALTER TABLE `runalyze_user` CHANGE `weight` `weight` DECIMAL( 4, 1 ) NOT NULL DEFAULT '0.0';

/* Rev175 */
ALTER DATABASE runalyze CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;

ALTER TABLE runalyze_clothes DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE runalyze_conf DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE runalyze_dataset DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE runalyze_plugin DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE runalyze_shoe DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE runalyze_sport DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE runalyze_training DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE runalyze_type DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE runalyze_user DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

/* Rev180 */
ALTER TABLE `runalyze_dataset` ADD `label` VARCHAR( 100 ) NOT NULL AFTER `name`;

UPDATE `runalyze_dataset` SET `label` = 'Sportart' WHERE `name`="sportid";
UPDATE `runalyze_dataset` SET `label` = 'Trainingstyp' WHERE `name`="typeid";
UPDATE `runalyze_dataset` SET `label` = 'Uhrzeit' WHERE `name`="time";
UPDATE `runalyze_dataset` SET `label` = 'Distanz' WHERE `name`="distance";
UPDATE `runalyze_dataset` SET `label` = 'Dauer' WHERE `name`="s";
UPDATE `runalyze_dataset` SET `label` = 'Pace' WHERE `name`="pace";
UPDATE `runalyze_dataset` SET `label` = 'H&ouml;henmeter' WHERE `name`="elevation";
UPDATE `runalyze_dataset` SET `label` = 'Kalorien' WHERE `name`="kcal";
UPDATE `runalyze_dataset` SET `label` = 'durchschn. Puls' WHERE `name`="pulse_avg";
UPDATE `runalyze_dataset` SET `label` = 'max. Puls' WHERE `name`="pulse_max";
UPDATE `runalyze_dataset` SET `label` = 'TRIMP' WHERE `name`="trimp";
UPDATE `runalyze_dataset` SET `label` = 'Temperatur' WHERE `name`="temperature";
UPDATE `runalyze_dataset` SET `label` = 'Wetter' WHERE `name`="weatherid";
UPDATE `runalyze_dataset` SET `label` = 'Strecke' WHERE `name`="route";
UPDATE `runalyze_dataset` SET `label` = 'Kleidung' WHERE `name`="clothes";
UPDATE `runalyze_dataset` SET `label` = 'Zwischenzeiten' WHERE `name`="splits";
UPDATE `runalyze_dataset` SET `label` = 'Bemerkung' WHERE `name`="comment";
UPDATE `runalyze_dataset` SET `label` = 'Schuh' WHERE `name`="shoeid";
UPDATE `runalyze_dataset` SET `label` = 'VDOT' WHERE `name`="vdot";
UPDATE `runalyze_dataset` SET `label` = 'Trainingspartner' WHERE `name`="partner";
UPDATE `runalyze_dataset` SET `label` = 'Lauf-ABC' WHERE `name`="abc";

/* Rev185 */
ALTER TABLE `runalyze_shoe` ADD `additionalKm` DECIMAL( 6, 2 ) NOT NULL DEFAULT '0';

/* Rev190 */
CREATE TABLE IF NOT EXISTS `runalyze_account` ( `id` int(11) NOT NULL AUTO_INCREMENT, `username` varchar(60) NOT NULL, `name` varchar(50) NOT NULL, `mail` varchar(100) NOT NULL, `password` varchar(64) NOT NULL, `session_id` int(32) DEFAULT NULL, PRIMARY KEY (`id`), UNIQUE KEY `username` (`username`), UNIQUE KEY `mail` (`mail`), UNIQUE KEY `session_id` (`session_id`) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

ALTER TABLE  `runalyze_clothes` ADD  `accountid` INT( 11 ) NOT NULL;
ALTER TABLE  `runalyze_conf` ADD  `accountid` INT( 11 ) NOT NULL;
ALTER TABLE  `runalyze_dataset` ADD  `accountid` INT( 11 ) NOT NULL;
ALTER TABLE  `runalyze_plugin` ADD  `accountid` INT( 11 ) NOT NULL;
ALTER TABLE  `runalyze_shoe` ADD  `accountid` INT( 11 ) NOT NULL;
ALTER TABLE  `runalyze_sport` ADD  `accountid` INT( 11 ) NOT NULL;
ALTER TABLE  `runalyze_training` ADD  `accountid` INT( 11 ) NOT NULL;
ALTER TABLE  `runalyze_type` ADD  `accountid` INT( 11 ) NOT NULL;
ALTER TABLE  `runalyze_user` ADD  `accountid` INT( 11 ) NOT NULL;

ALTER TABLE  `runalyze_conf` DROP INDEX  `key`;

/* Rev193 */
ALTER TABLE `runalyze_account` ADD `registerdate` INT NOT NULL, ADD `lastaction` INT NOT NULL, ADD `lastlogin` INT NOT NULL, ADD `autologin_hash` VARCHAR( 32 ) NOT NULL, ADD `changepw_hash` VARCHAR( 32 ) NOT NULL, ADD `changepw_timelimit` INT NOT NULL, ADD `activation_hash` VARCHAR( 32 ) NOT NULL;
ALTER TABLE `runalyze_account` CHANGE `session_id` `session_id` VARCHAR( 32 ) NULL DEFAULT NULL;

/* Rev196 */
DROP TABLE `runalyze_weather`;

/* Rev219 */
ALTER TABLE `runalyze_training` ADD `is_public` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `time`;
ALTER TABLE `runalyze_training` ADD `notes` TEXT NOT NULL AFTER `shoeid`;
ALTER TABLE `runalyze_plugin` ADD `internal_data` TEXT NOT NULL AFTER `config`;