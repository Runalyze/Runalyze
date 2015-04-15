/* 20.03.2015 - add vdot value to dataset */
UPDATE `runalyze_dataset` set name = 'vdoticon' WHERE name = 'vdot';
INSERT INTO `runalyze_dataset` (`name`, `active`, `modus`, `class`, `style`, `position`, `summary`, `summary_mode`, `accountid`) SELECT 'vdot', 1, 2, '', '', 0, 1, 'AVG', `id` FROM `runalyze_account`;

/* 11.04.2015 - remove pace unit 'none' */
UPDATE `runalyze_sport` SET `speed`="km/h" WHERE `speed`="";

/* 12.04.2015 - remove long run type id */
UPDATE `runalyze_conf` SET `value`="race" WHERE `key`="TRAINING_MAP_PUBLIC_MODE" AND `value`="race-longjog";
DELETE FROM `runalyze_conf` WHERE `key`="TYPE_ID_LONGRUN";

/* 12.04.2015 - update sport icons */
UPDATE runalyze_sport SET img="icons8-Sports-Mode" where `img`="icons8-sports_mode";
UPDATE runalyze_sport SET img="icons8-Running" where `img`="icons8-running";
UPDATE runalyze_sport SET img="icons8-Regular-Biking" where `img`="icons8-regular_biking";
UPDATE runalyze_sport SET img="icons8-Swimming" where `img`="icons8-swimming";
UPDATE runalyze_sport SET img="icons8-Yoga" where `img`="icons8-yoga";

/* 12.04.2015 - remove distances/pulse from sport configuration */
ALTER TABLE `runalyze_sport` DROP `distances`, DROP `pulse`;

/* 15.04.2015 - add language to account configuration */
ALTER TABLE `runalyze_account` ADD `language` VARCHAR(5) NOT NULL AFTER `mail`;