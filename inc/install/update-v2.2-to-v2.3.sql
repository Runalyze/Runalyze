/* 18.10.2015 - drop 'types' flag for sport */
ALTER TABLE `runalyze_sport` DROP `types`;

/* 21.11.2015 - add gctb and arr_vertical_ratio to trackdata */
ALTER TABLE `runalyze_trackdata` ADD `gctb` LONGTEXT NOT NULL, ADD `vertical_ratio` LONGTEXT NOT NULL;
ALTER TABLE `runalyze_training` ADD  `vertical_ratio` SMALLINT UNSIGNED NOT NULL DEFAULT  '0', ADD  `gctb` SMALLINT UNSIGNED AFTER  `groundcontact`;