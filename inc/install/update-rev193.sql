UPDATE `runalyze_weather` SET `img` = 'ka.png' WHERE `runalyze_weather`.`id` =1;
UPDATE `runalyze_weather` SET `img` = 'sonnig.png' WHERE `runalyze_weather`.`id` =2;
UPDATE `runalyze_weather` SET `img` = 'heiter.png' WHERE `runalyze_weather`.`id` =3;
UPDATE `runalyze_weather` SET `img` = 'bewoelkt.png' WHERE `runalyze_weather`.`id` =4;
UPDATE `runalyze_weather` SET `img` = 'wechselhaft.png' WHERE `runalyze_weather`.`id` =5;
UPDATE `runalyze_weather` SET `img` = 'regnerisch.png' WHERE `runalyze_weather`.`id` =6;
UPDATE `runalyze_weather` SET `img` = 'Schnee.png' WHERE `runalyze_weather`.`id` =7;

ALTER TABLE `runalyze_account` ADD `registerdate` INT NOT NULL, ADD `lastaction` INT NOT NULL, ADD `lastlogin` INT NOT NULL, ADD `autologin_hash` VARCHAR( 32 ) NOT NULL, ADD `changepw_hash` VARCHAR( 32 ) NOT NULL, ADD `changepw_timelimit` INT NOT NULL;
ALTER TABLE `runalyze_account` CHANGE `session_id` `session_id` VARCHAR( 32 ) NULL DEFAULT NULL;