/* Rev 736 */
INSERT INTO `runalyze_dataset` (`name`, `label`, `description`, `distance`, `outside`, `pulse`, `type`, `modus`, `class`, `style`, `position`, `summary`, `summary_mode`, `accountid`) SELECT 'jd_intensity', 'JD-Intensit&auml;t', 'Anzeige der Trainingspunkte nacht Jack Daniels', 1, 0, 1, 1, 1, '', '', 22, 1, 'SUM', `id` FROM `runalyze_account`;

/* Rev 794 */
ALTER TABLE `runalyze_plugin` DROP `name`, DROP `description`;

/* Rev 798 */
CREATE TABLE IF NOT EXISTS `runalyze_plugin_conf` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pluginid` int(10) unsigned NOT NULL,
  `config` varchar(100) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY (`pluginid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


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

/* Rev 799 */
ALTER TABLE  `runalyze_conf` CHANGE  `value`  `value` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL;

ALTER TABLE  `runalyze_training` CHANGE  `temperature`  `temperature` TINYINT NULL DEFAULT NULL;

/* Rev ? - Refactor configuration */
/*  - add new column */
ALTER TABLE  `runalyze_conf` ADD  `category` VARCHAR( 32 ) NOT NULL AFTER  `id`;
/*  - set categories */
UPDATE `runalyze_conf` SET `category`="general" WHERE `key`="GENDER" OR `key`="PULS_MODE" OR `key`="MAINSPORT" OR `key`="RUNNINGSPORT" OR `key`="WK_TYPID" OR `key`="LL_TYPID";
UPDATE `runalyze_conf` SET `category`="activity-view" WHERE `key`="TRAINING_PLOT_SMOOTH" OR `key`="TRAINING_DECIMALS" OR `key`="TRAINING_PLOT_PRECISION" OR `key`="GMAP_PATH_PRECISION" OR `key`="ELEVATION_METHOD" OR `key`="GMAP_PATH_BREAK" OR `key`="ELEVATION_MIN_DIFF" OR `key`="TRAINING_MAP_COLOR" OR `key`="PACE_Y_LIMIT_MAX" OR `key`="PACE_Y_AXIS_REVERSE" OR `key`="PACE_Y_LIMIT_MIN" OR `key`="PACE_HIDE_OUTLIERS" OR `key`="TRAINING_PLOT_MODE" OR `key`="TRAINING_MAP_BEFORE_PLOTS" OR `key`="TRAINING_LEAFLET_LAYER";
UPDATE `runalyze_conf` SET `category`="privacy" WHERE `key`="TRAINING_MAKE_PUBLIC" OR `key`="TRAINING_LIST_PUBLIC" OR `key`="TRAINING_LIST_ALL" OR `key`="TRAINING_LIST_STATISTICS" OR `key`="TRAINING_MAP_PUBLIC_MODE";
UPDATE `runalyze_conf` SET `category`="activity-form" WHERE `key`="FORMULAR_SHOW_SPORT" OR `key`="FORMULAR_SHOW_GENERAL" OR `key`="FORMULAR_SHOW_DISTANCE" OR `key`="FORMULAR_SHOW_SPLITS" OR `key`="FORMULAR_SHOW_WEATHER" OR `key`="FORMULAR_SHOW_OTHER" OR `key`="FORMULAR_SHOW_NOTES" OR `key`="FORMULAR_SHOW_PUBLIC" OR `key`="FORMULAR_SHOW_ELEVATION" OR `key`="FORMULAR_SHOW_GPS";
UPDATE `runalyze_conf` SET `category`="data-browser" WHERE `key`="DB_DISPLAY_MODE" OR `key`="DB_SHOW_DIRECT_EDIT_LINK" OR `key`="DB_SHOW_CREATELINK_FOR_DAYS";

/*  - rename some values */

/*  - remove unused values */
DELETE FROM `runalyze_conf` WHERE `key`="DB_HIGHLIGHT_TODAY";