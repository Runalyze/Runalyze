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
ALTER TABLE  `runalyze_weather` ADD  `accountid` INT( 11 ) NOT NULL;

ALTER TABLE  `runalyze_conf` DROP INDEX  `key`;