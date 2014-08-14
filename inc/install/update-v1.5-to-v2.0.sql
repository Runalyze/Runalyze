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