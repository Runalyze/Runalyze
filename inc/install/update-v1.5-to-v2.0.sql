/* Rev 736 - 05.03.2014 */
INSERT INTO `runalyze_dataset` (`name`, `label`, `description`, `distance`, `outside`, `pulse`, `type`, `modus`, `class`, `style`, `position`, `summary`, `summary_mode`, `accountid`) SELECT 'jd_intensity', 'JD-Intensit&auml;t', 'Anzeige der Trainingspunkte nacht Jack Daniels', 1, 0, 1, 1, 1, '', '', 22, 1, 'SUM', `id` FROM `runalyze_account`;


/* Rev 794 - 10.08.2014 */
ALTER TABLE `runalyze_plugin` DROP `name`, DROP `description`;


/* Rev 798 - 13.08.2014 */
CREATE TABLE IF NOT EXISTS `runalyze_plugin_conf` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pluginid` int(10) unsigned NOT NULL,
  `config` varchar(100) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY (`pluginid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO `runalyze_plugin_conf` (`pluginid`, `config`, `value`) SELECT PluginList.id, SUBSTRING_INDEX(PluginList.config, "|", 1) as config_key, SUBSTRING_INDEX(SUBSTRING_INDEX(PluginList.config, "|", 2), "=", -1) as config_value FROM (SELECT `id`, SUBSTRING_INDEX(SUBSTRING_INDEX(`config`, "\n", 1), "\n", -1) as `config` FROM runalyze_plugin) AS PluginList WHERE PluginList.config != "";
INSERT INTO `runalyze_plugin_conf` (`pluginid`, `config`, `value`) SELECT PluginList.id, SUBSTRING_INDEX(PluginList.config, "|", 1) as config_key, SUBSTRING_INDEX(SUBSTRING_INDEX(PluginList.config, "|", 2), "=", -1) as config_value FROM (SELECT `id`, SUBSTRING_INDEX(SUBSTRING_INDEX(`config`, "\n", 2), "\n", -1) as `config` FROM runalyze_plugin) AS PluginList WHERE PluginList.config != "";
INSERT INTO `runalyze_plugin_conf` (`pluginid`, `config`, `value`) SELECT PluginList.id, SUBSTRING_INDEX(PluginList.config, "|", 1) as config_key, SUBSTRING_INDEX(SUBSTRING_INDEX(PluginList.config, "|", 2), "=", -1) as config_value FROM (SELECT `id`, SUBSTRING_INDEX(SUBSTRING_INDEX(`config`, "\n", 3), "\n", -1) as `config` FROM runalyze_plugin) AS PluginList WHERE PluginList.config != "";
INSERT INTO `runalyze_plugin_conf` (`pluginid`, `config`, `value`) SELECT PluginList.id, SUBSTRING_INDEX(PluginList.config, "|", 1) as config_key, SUBSTRING_INDEX(SUBSTRING_INDEX(PluginList.config, "|", 2), "=", -1) as config_value FROM (SELECT `id`, SUBSTRING_INDEX(SUBSTRING_INDEX(`config`, "\n", 4), "\n", -1) as `config` FROM runalyze_plugin) AS PluginList WHERE PluginList.config != "";
INSERT INTO `runalyze_plugin_conf` (`pluginid`, `config`, `value`) SELECT PluginList.id, SUBSTRING_INDEX(PluginList.config, "|", 1) as config_key, SUBSTRING_INDEX(SUBSTRING_INDEX(PluginList.config, "|", 2), "=", -1) as config_value FROM (SELECT `id`, SUBSTRING_INDEX(SUBSTRING_INDEX(`config`, "\n", 5), "\n", -1) as `config` FROM runalyze_plugin) AS PluginList WHERE PluginList.config != "";
INSERT INTO `runalyze_plugin_conf` (`pluginid`, `config`, `value`) SELECT PluginList.id, SUBSTRING_INDEX(PluginList.config, "|", 1) as config_key, SUBSTRING_INDEX(SUBSTRING_INDEX(PluginList.config, "|", 2), "=", -1) as config_value FROM (SELECT `id`, SUBSTRING_INDEX(SUBSTRING_INDEX(`config`, "\n", 6), "\n", -1) as `config` FROM runalyze_plugin) AS PluginList WHERE PluginList.config != "";
INSERT INTO `runalyze_plugin_conf` (`pluginid`, `config`, `value`) SELECT PluginList.id, SUBSTRING_INDEX(PluginList.config, "|", 1) as config_key, SUBSTRING_INDEX(SUBSTRING_INDEX(PluginList.config, "|", 2), "=", -1) as config_value FROM (SELECT `id`, SUBSTRING_INDEX(SUBSTRING_INDEX(`config`, "\n", 7), "\n", -1) as `config` FROM runalyze_plugin) AS PluginList WHERE PluginList.config != "";
INSERT INTO `runalyze_plugin_conf` (`pluginid`, `config`, `value`) SELECT PluginList.id, SUBSTRING_INDEX(PluginList.config, "|", 1) as config_key, SUBSTRING_INDEX(SUBSTRING_INDEX(PluginList.config, "|", 2), "=", -1) as config_value FROM (SELECT `id`, SUBSTRING_INDEX(SUBSTRING_INDEX(`config`, "\n", 8), "\n", -1) as `config` FROM runalyze_plugin) AS PluginList WHERE PluginList.config != "";

UPDATE `runalyze_plugin_conf` SET `value`=(`value`="true") WHERE `value` IN("true", "false");

ALTER TABLE `runalyze_plugin` DROP `config`, DROP `internal_data`;

ALTER TABLE  `runalyze_plugin` CHANGE  `type`  `type` ENUM(  'panel',  'stat',  'tool' ) NOT NULL;


/* 14.08.2014 */
ALTER TABLE  `runalyze_conf` CHANGE  `value`  `value` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL;

ALTER TABLE  `runalyze_training` CHANGE  `temperature`  `temperature` TINYINT NULL DEFAULT NULL;


/* 21.09.2014 - Refactor configuration */
/*  - add new column */
ALTER TABLE  `runalyze_conf` ADD  `category` VARCHAR( 32 ) NOT NULL AFTER  `id`;
/*  - set categories */
UPDATE `runalyze_conf` SET `category`="general" WHERE `key`="GENDER" OR `key`="PULS_MODE" OR `key`="MAINSPORT" OR `key`="RUNNINGSPORT" OR `key`="WK_TYPID" OR `key`="LL_TYPID";
UPDATE `runalyze_conf` SET `category`="activity-view" WHERE `key`="TRAINING_PLOT_SMOOTH" OR `key`="TRAINING_DECIMALS" OR `key`="TRAINING_PLOT_PRECISION" OR `key`="GMAP_PATH_PRECISION" OR `key`="ELEVATION_METHOD" OR `key`="GMAP_PATH_BREAK" OR `key`="ELEVATION_MIN_DIFF" OR `key`="TRAINING_MAP_COLOR" OR `key`="PACE_Y_LIMIT_MAX" OR `key`="PACE_Y_AXIS_REVERSE" OR `key`="PACE_Y_LIMIT_MIN" OR `key`="PACE_HIDE_OUTLIERS" OR `key`="TRAINING_PLOT_MODE" OR `key`="TRAINING_MAP_BEFORE_PLOTS" OR `key`="TRAINING_LEAFLET_LAYER";
UPDATE `runalyze_conf` SET `category`="privacy" WHERE `key`="TRAINING_MAKE_PUBLIC" OR `key`="TRAINING_LIST_PUBLIC" OR `key`="TRAINING_LIST_ALL" OR `key`="TRAINING_LIST_STATISTICS" OR `key`="TRAINING_MAP_PUBLIC_MODE";
UPDATE `runalyze_conf` SET `category`="activity-form" WHERE `key`="FORMULAR_SHOW_SPORT" OR `key`="FORMULAR_SHOW_GENERAL" OR `key`="FORMULAR_SHOW_DISTANCE" OR `key`="FORMULAR_SHOW_SPLITS" OR `key`="FORMULAR_SHOW_WEATHER" OR `key`="FORMULAR_SHOW_OTHER" OR `key`="FORMULAR_SHOW_NOTES" OR `key`="FORMULAR_SHOW_PUBLIC" OR `key`="FORMULAR_SHOW_ELEVATION" OR `key`="FORMULAR_SHOW_GPS";
UPDATE `runalyze_conf` SET `category`="data-browser" WHERE `key`="DB_DISPLAY_MODE" OR `key`="DB_SHOW_DIRECT_EDIT_LINK" OR `key`="DB_SHOW_CREATELINK_FOR_DAYS";
UPDATE `runalyze_conf` SET `category`="design" WHERE `key`="DESIGN_BG_FILE";
UPDATE `runalyze_conf` SET `category`="vdot" WHERE `key`="VDOT_HF_METHOD" OR `key`="VDOT_DAYS" OR `key`="JD_USE_VDOT_CORRECTOR" OR `key`="VDOT_MANUAL_CORRECTOR" OR `key`="VDOT_MANUAL_VALUE" OR `key`="JD_USE_VDOT_CORRECTION_FOR_ELEVATION" OR `key`="VDOT_CORRECTION_POSITIVE_ELEVATION" OR `key`="VDOT_CORRECTION_NEGATIVE_ELEVATION";
UPDATE `runalyze_conf` SET `category`="trimp" WHERE `key`="ATL_DAYS" OR `key`="CTL_DAYS";
UPDATE `runalyze_conf` SET `category`="misc" WHERE `key`="RESULTS_AT_PAGE";
UPDATE `runalyze_conf` SET `category`="activity-form" WHERE `key`="TRAINING_CREATE_MODE" OR `key`="TRAINING_SHOW_AFTER_CREATE" OR `key`="TRAINING_DO_ELEVATION" OR `key`="TRAINING_ELEVATION_SERVER" OR `key`="TRAINING_LOAD_WEATHER" OR `key`="PLZ" OR `key`="COMPUTE_KCAL" OR `key`="COMPUTE_POWER" OR `key`="TRAINING_SORT_SPORTS" OR `key`="TRAINING_SORT_TYPES" OR `key`="TRAINING_SORT_SHOES" OR `key`="GARMIN_IGNORE_IDS";
UPDATE `runalyze_conf` SET `category`="data" WHERE `key`="START_TIME" OR `key`="HF_MAX" OR `key`="HF_REST" OR `key`="VDOT_FORM" OR `key`="VDOT_CORRECTOR" OR `key`="BASIC_ENDURANCE" OR `key`="MAX_ATL" OR `key`="MAX_CTL" OR `key`="MAX_TRIMP";

/*  - rename some values */
UPDATE `runalyze_conf` SET `key`="HEART_RATE_UNIT" WHERE `key`="PULS_MODE";
UPDATE `runalyze_conf` SET `key`="TYPE_ID_RACE" WHERE `key`="WK_TYPID";
UPDATE `runalyze_conf` SET `key`="TYPE_ID_LONGRUN" WHERE `key`="LL_TYPID";
UPDATE `runalyze_conf` SET `key`="VDOT_USE_CORRECTION" WHERE `key`="JD_USE_VDOT_CORRECTOR";
UPDATE `runalyze_conf` SET `key`="VDOT_USE_CORRECTION_FOR_ELEVATION" WHERE `key`="JD_USE_VDOT_CORRECTION_FOR_ELEVATION";
UPDATE `runalyze_conf` SET `key`="SEARCH_RESULTS_PER_PAGE" WHERE `key`="RESULTS_AT_PAGE";
UPDATE `runalyze_conf` SET `key`="ELEVATION_TRESHOLD" WHERE `key`="ELEVATION_MIN_DIFF";

/*  - remove unused values */
DELETE FROM `runalyze_conf` WHERE `key`="DB_HIGHLIGHT_TODAY";
DELETE FROM `runalyze_conf` WHERE `key`="DESIGN_BG_FIX_AND_STRETCH";
DELETE FROM `runalyze_conf` WHERE `key`="RECHENSPIELE";
DELETE FROM `runalyze_conf` WHERE `key`="TRAINING_MAP_MARKER" OR `key`="TRAINING_SHOW_DETAILS" OR `key`="TRAINING_SHOW_ZONES" OR `key`="TRAINING_SHOW_ROUNDS" OR `key`="TRAINING_SHOW_GRAPHICS" OR `key`="TRAINING_SHOW_PLOT_PACE" OR `key`="TRAINING_SHOW_PLOT_PULSE" OR `key`="TRAINING_SHOW_PLOT_ELEVATION" OR `key`="TRAINING_SHOW_PLOT_SPLITS" OR `key`="TRAINING_SHOW_PLOT_PACEPULSE" OR `key`="TRAINING_SHOW_PLOT_COLLECTION" OR `key`="TRAINING_SHOW_PLOT_CADENCE" OR `key`="TRAINING_SHOW_PLOT_POWER" OR `key`="TRAINING_SHOW_PLOT_TEMPERATURE" OR `key`="TRAINING_SHOW_MAP";
DELETE FROM `runalyze_conf` WHERE `key`="GARMIN_API_KEY";
DELETE FROM `runalyze_conf` WHERE `key`="TRAINING_MAP_BEFORE_PLOTS";
DELETE FROM `runalyze_conf` WHERE `key`="USE_WETTER";
DELETE FROM `runalyze_conf` WHERE `key`="USE_PULS";
DELETE FROM `runalyze_conf` WHERE `key`="TRAINING_MAPTYPE";

/*  - update some values */
UPDATE `runalyze_conf` SET `value`="google" WHERE `key`="TRAINING_ELEVATION_SERVER" AND `value`="google=true|geonames=false";
UPDATE `runalyze_conf` SET `value`="geonames" WHERE `key`="TRAINING_ELEVATION_SERVER" AND `value`="google=true|geonames=false";
UPDATE `runalyze_conf` SET `value`="m" WHERE `key`="GENDER" AND `value`="m=true|f=false";
UPDATE `runalyze_conf` SET `value`="f" WHERE `key`="GENDER" AND `value`="m=false|f=true";
UPDATE `runalyze_conf` SET `value`="0" WHERE `key`="TRAINING_DECIMALS" AND `value`="0=true|1=false|2=false";
UPDATE `runalyze_conf` SET `value`="1" WHERE `key`="TRAINING_DECIMALS" AND `value`="0=false|1=true|2=false";
UPDATE `runalyze_conf` SET `value`="2" WHERE `key`="TRAINING_DECIMALS" AND `value`="0=false|1=false|2=true";
UPDATE `runalyze_conf` SET `value`="bpm" WHERE `key`="HEART_RATE_UNIT" AND (`value`="bpm=true|hfmax=false" OR `value`="bpm=true|hfmax=false|hfres=false");
UPDATE `runalyze_conf` SET `value`="hfmax" WHERE `key`="HEART_RATE_UNIT" AND (`value`="bpm=false|hfmax=true" OR `value`="bpm=false|hfmax=true|hfres=false");
UPDATE `runalyze_conf` SET `value`="hfres" WHERE `key`="HEART_RATE_UNIT" AND `value`="bpm=false|hfmax=false|hfres=true";
UPDATE `runalyze_conf` SET `value`="upload" WHERE `key`="TRAINING_CREATE_MODE" AND `value`="upload=true|garmin=false|form=false";
UPDATE `runalyze_conf` SET `value`="garmin" WHERE `key`="TRAINING_CREATE_MODE" AND `value`="upload=false|garmin=true|form=false";
UPDATE `runalyze_conf` SET `value`="form" WHERE `key`="TRAINING_CREATE_MODE" AND `value`="upload=false|garmin=false|form=true";


/* 21.09.2014 - make dataset translatable */
ALTER TABLE `runalyze_dataset` DROP `label`, DROP `description`, DROP `distance`, DROP `outside`, DROP `pulse`, DROP `type`;


/* 16.10.2014 - add weight for shoes */
ALTER TABLE `runalyze_shoe` ADD `weight` SMALLINT UNSIGNED NOT NULL AFTER `since`;


/* 30.10.2014 - add salt to account table */
ALTER TABLE  `runalyze_account` ADD  `salt` CHAR( 64 ) NOT NULL AFTER  `password`;


/* 02.11.2014 - refactor database for activity data */
ALTER TABLE  `runalyze_training` ADD  `routeid` INT UNSIGNED NOT NULL AFTER  `route`;
ALTER TABLE  `runalyze_training` ADD  `groundcontact` SMALLINT UNSIGNED NOT NULL DEFAULT  '0' AFTER  `power`;
ALTER TABLE  `runalyze_training` ADD  `vertical_oscillation` TINYINT UNSIGNED NOT NULL DEFAULT  '0' AFTER  `groundcontact`;

INSERT INTO `runalyze_dataset` (`name`, `active`, `modus`, `class`, `style`, `position`, `summary`, `summary_mode`, `accountid`) SELECT 'groundcontact', 1, 1, 'small', '', 25, 1, 'AVG', `id` FROM `runalyze_account`;
INSERT INTO `runalyze_dataset` (`name`, `active`, `modus`, `class`, `style`, `position`, `summary`, `summary_mode`, `accountid`) SELECT 'vertical_oscillation', 1, 1, 'small', '', 26, 1, 'AVG', `id` FROM `runalyze_account`;

/* Allow InnoDB row_format = DYNAMIC */
SET GLOBAL innodb_file_format=barracuda; SET GLOBAL innodb_file_per_table=ON;

CREATE TABLE IF NOT EXISTS `runalyze_trackdata`(
  `accountid` INT UNSIGNED NOT NULL,
  `activityid` INT UNSIGNED NOT NULL,
  `time` LONGTEXT NOT NULL,
  `distance` LONGTEXT NOT NULL,
  `pace` LONGTEXT NOT NULL,
  `heartrate` LONGTEXT NOT NULL,
  `cadence` LONGTEXT NOT NULL,
  `power` LONGTEXT NOT NULL,
  `temperature` LONGTEXT NOT NULL,
  `groundcontact` LONGTEXT NOT NULL,
  `vertical_oscillation` LONGTEXT NOT NULL,
  `pauses` TEXT NOT NULL,
  PRIMARY KEY (`activityid`),
  KEY `accountid` (`accountid`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `runalyze_route` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `accountid` INT UNSIGNED NOT NULL,
  `name` VARCHAR( 255 ) NOT NULL,
  `cities` VARCHAR( 255 ) NOT NULL,
  `distance` DECIMAL( 6, 2 ) UNSIGNED NOT NULL,
  `elevation` SMALLINT UNSIGNED NOT NULL,
  `elevation_up` SMALLINT UNSIGNED NOT NULL,
  `elevation_down` SMALLINT UNSIGNED NOT NULL,
  `lats` LONGTEXT NOT NULL,
  `lngs` LONGTEXT NOT NULL,
  `elevations_original` LONGTEXT NOT NULL,
  `elevations_corrected` LONGTEXT NOT NULL,
  `elevations_source` VARCHAR( 255 ) NOT NULL,
  `startpoint_lat` FLOAT( 8, 5 ) NOT NULL,
  `startpoint_lng` FLOAT( 8, 5 ) NOT NULL,
  `endpoint_lat` FLOAT( 8, 5 ) NOT NULL,
  `endpoint_lng` FLOAT( 8, 5 ) NOT NULL,
  `min_lat` FLOAT( 8, 5 ) NOT NULL,
  `min_lng` FLOAT( 8, 5 ) NOT NULL,
  `max_lat` FLOAT( 8, 5 ) NOT NULL,
  `max_lng` FLOAT( 8, 5 ) NOT NULL,
  `in_routenet` TINYINT( 1 ) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `accountid` (`accountid`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

/* 17.01.2015 - old columns of `runalyze_training` are removed by 'refactor-db.php' */

/* 19.01.2015 - Change to InnoDB */
ALTER TABLE `runalyze_user` ENGINE=InnoDB;
ALTER TABLE `runalyze_account` ENGINE=InnoDB;
ALTER TABLE `runalyze_sport` ENGINE=InnoDB;
ALTER TABLE `runalyze_conf` ENGINE=InnoDB;
ALTER TABLE `runalyze_plugin` ENGINE=InnoDB;
ALTER TABLE `runalyze_clothes` ENGINE=InnoDB;
ALTER TABLE `runalyze_type` ENGINE=InnoDB;
ALTER TABLE `runalyze_dataset` ENGINE=InnoDB;
ALTER TABLE `runalyze_shoe` ENGINE=InnoDB;
ALTER TABLE `runalyze_training` ENGINE=InnoDB;
