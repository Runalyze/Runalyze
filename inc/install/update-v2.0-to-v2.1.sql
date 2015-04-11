/* 20.03.2015 - add vdot value to dataset */
UPDATE `runalyze_dataset` set name = 'vdoticon' WHERE name = 'vdot';
INSERT INTO `runalyze_dataset` (`name`, `active`, `modus`, `class`, `style`, `position`, `summary`, `summary_mode`, `accountid`) SELECT 'vdot', 1, 2, '', '', 0, 1, 'AVG', `id` FROM `runalyze_account`;

/* 11.04.2015 - remove pace unit 'none' */
UPDATE `runalyze_sport` SET `speed`="km/h" WHERE `speed`="";