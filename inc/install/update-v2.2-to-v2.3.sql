/* 18.10.2015 - drop 'types' flag for sport */
ALTER TABLE `runalyze_sport` DROP `types`;

/* 21.11.2015 - add groundcontact_balance and arr_vertical_ratio to trackdata */
ALTER TABLE `runalyze_trackdata` ADD `groundcontact_balance` LONGTEXT NOT NULL;
ALTER TABLE `runalyze_training` ADD  `vertical_ratio` SMALLINT UNSIGNED NOT NULL DEFAULT  '0', ADD  `groundcontact_balance` SMALLINT UNSIGNED AFTER  `groundcontact`;