/* 03.02.2015 - Change charset to utf-8 */
ALTER TABLE `runalyze_clothes` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `runalyze_conf` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `runalyze_dataset` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `runalyze_plugin` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `runalyze_shoe` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `runalyze_sport` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `runalyze_training` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `runalyze_type` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;

/* 07.02.2015 - New icons */
UPDATE runalyze_sport SET img='icons8-running'	where img IN	('laufen.gif');
UPDATE runalyze_sport SET img='icons8-regular_biking'	where img IN	('radfahren.gif');
UPDATE runalyze_sport SET img='icons8-swimming'	where img IN	('schwimmen.gif');
UPDATE runalyze_sport SET img='icons8-yoga'	where img IN	('gymnastik.gif');
UPDATE runalyze_sport SET img='icons8-sports_mode'	where img IN	('unknown.gif','krafttraining.gif','wandern.gif','teamsport.gif','bogenschiessen.gif','inlineskating.gif','taekwondo.gif');

/* 09.02.2015 - Fix route for dataset */
UPDATE `runalyze_dataset` SET `name`="routeid" WHERE `name`="route";

/* 12.02.2015 - Remove old configuration */
DELETE FROM `runalyze_conf` WHERE `key`="TRAINING_ELEVATION_SERVER";
