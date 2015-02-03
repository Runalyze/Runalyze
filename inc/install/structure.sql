-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 02. November 2014 um 12:30
-- Server Version: 5.1.44
-- PHP-Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `runalyze`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_account`
--

CREATE TABLE IF NOT EXISTS `runalyze_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(60) NOT NULL,
  `name` varchar(50) NOT NULL,
  `mail` varchar(100) NOT NULL,
  `password` varchar(64) NOT NULL,
  `salt` char(64) NOT NULL,
  `session_id` varchar(32) DEFAULT NULL,
  `registerdate` int(11) NOT NULL,
  `lastaction` int(11) NOT NULL,
  `lastlogin` int(11) NOT NULL,
  `autologin_hash` varchar(32) NOT NULL,
  `changepw_hash` varchar(32) NOT NULL,
  `changepw_timelimit` int(11) NOT NULL,
  `activation_hash` varchar(32) NOT NULL,
  `deletion_hash` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `mail` (`mail`),
  UNIQUE KEY `session_id` (`session_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Trigger `runalyze_account`
--
DROP TRIGGER IF EXISTS `del_tr_train`;
DELIMITER //
CREATE TRIGGER `del_tr_train` AFTER DELETE ON `runalyze_account`
 FOR EACH ROW BEGIN
		DELETE FROM runalyze_clothes WHERE accountid = OLD.id;
		DELETE FROM runalyze_conf WHERE accountid = OLD.id;
		DELETE FROM runalyze_dataset WHERE accountid = OLD.id;
		DELETE FROM runalyze_plugin WHERE accountid = OLD.id;
		DELETE FROM runalyze_shoe WHERE accountid = OLD.id;
		DELETE FROM runalyze_sport WHERE accountid = OLD.id;
		DELETE FROM runalyze_training WHERE accountid = OLD.id;
		DELETE FROM runalyze_type WHERE accountid = OLD.id;
		DELETE FROM runalyze_user WHERE accountid = OLD.id;
	END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_clothes`
--

CREATE TABLE IF NOT EXISTS `runalyze_clothes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `short` varchar(20) NOT NULL,
  `order` tinyint(1) NOT NULL,
  `accountid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `accountid` (`accountid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_conf`
--

CREATE TABLE IF NOT EXISTS `runalyze_conf` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(32) NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` varchar(255) NOT NULL,
  `accountid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `accountid` (`accountid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_dataset`
--

CREATE TABLE IF NOT EXISTS `runalyze_dataset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `modus` tinyint(1) NOT NULL DEFAULT '0',
  `class` varchar(25) NOT NULL,
  `style` varchar(100) NOT NULL,
  `position` smallint(6) NOT NULL DEFAULT '0',
  `summary` tinyint(1) NOT NULL DEFAULT '0',
  `summary_mode` varchar(3) NOT NULL DEFAULT 'SUM',
  `accountid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `accountid` (`accountid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_plugin`
--

CREATE TABLE IF NOT EXISTS `runalyze_plugin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `type` enum('panel','stat','tool') NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `order` smallint(6) NOT NULL,
  `accountid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `accountid` (`accountid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_plugin_conf`
--

CREATE TABLE IF NOT EXISTS `runalyze_plugin_conf` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pluginid` int(10) unsigned NOT NULL,
  `config` varchar(100) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pluginid` (`pluginid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_route`
--

CREATE TABLE IF NOT EXISTS `runalyze_route` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `accountid` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `cities` varchar(255) NOT NULL,
  `distance` decimal(6,2) unsigned NOT NULL,
  `elevation` smallint(5) unsigned NOT NULL,
  `elevation_up` smallint(5) unsigned NOT NULL,
  `elevation_down` smallint(5) unsigned NOT NULL,
  `lats` longtext NOT NULL,
  `lngs` longtext NOT NULL,
  `elevations_original` longtext NOT NULL,
  `elevations_corrected` longtext NOT NULL,
  `elevations_source` varchar(255) NOT NULL,
  `startpoint_lat` float(8,5) NOT NULL,
  `startpoint_lng` float(8,5) NOT NULL,
  `endpoint_lat` float(8,5) NOT NULL,
  `endpoint_lng` float(8,5) NOT NULL,
  `min_lat` float(8,5) NOT NULL,
  `min_lng` float(8,5) NOT NULL,
  `max_lat` float(8,5) NOT NULL,
  `max_lng` float(8,5) NOT NULL,
  `in_routenet` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `accountid` (`accountid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_shoe`
--

CREATE TABLE IF NOT EXISTS `runalyze_shoe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `since` varchar(10) NOT NULL DEFAULT '01.01.2000',
  `weight` smallint(5) unsigned NOT NULL,
  `km` decimal(6,2) NOT NULL DEFAULT '0.00',
  `time` int(11) NOT NULL DEFAULT '0',
  `inuse` tinyint(1) NOT NULL DEFAULT '1',
  `additionalKm` decimal(6,2) NOT NULL DEFAULT '0.00',
  `accountid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `accountid` (`accountid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_sport`
--

CREATE TABLE IF NOT EXISTS `runalyze_sport` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `img` varchar(100) NOT NULL DEFAULT 'unknown.gif',
  `short` tinyint(1) NOT NULL DEFAULT '0',
  `kcal` smallint(4) NOT NULL DEFAULT '0',
  `HFavg` smallint(3) NOT NULL DEFAULT '120',
  `RPE` tinyint(2) NOT NULL DEFAULT '2',
  `distances` tinyint(1) NOT NULL DEFAULT '1',
  `speed` varchar(10) NOT NULL DEFAULT 'min/km',
  `types` tinyint(1) NOT NULL DEFAULT '0',
  `pulse` tinyint(1) NOT NULL DEFAULT '0',
  `power` tinyint(1) NOT NULL DEFAULT '0',
  `outside` tinyint(1) NOT NULL DEFAULT '0',
  `accountid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `accountid` (`accountid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_trackdata`
--

CREATE TABLE IF NOT EXISTS `runalyze_trackdata` (
  `accountid` int(10) unsigned NOT NULL,
  `activityid` int(10) unsigned NOT NULL,
  `time` longtext NOT NULL,
  `distance` longtext NOT NULL,
  `pace` longtext NOT NULL,
  `heartrate` longtext NOT NULL,
  `cadence` longtext NOT NULL,
  `power` longtext NOT NULL,
  `temperature` longtext NOT NULL,
  `groundcontact` longtext NOT NULL,
  `vertical_oscillation` longtext NOT NULL,
  `pauses` text NOT NULL,
  PRIMARY KEY (`activityid`),
  KEY `accountid` (`accountid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_training`
--

CREATE TABLE IF NOT EXISTS `runalyze_training` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sportid` int(11) NOT NULL DEFAULT '0',
  `typeid` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL,
  `edited` int(11) NOT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `is_track` tinyint(1) NOT NULL DEFAULT '0',
  `distance` decimal(6,2) NOT NULL DEFAULT '0.00',
  `s` decimal(8,2) NOT NULL DEFAULT '0.00',
  `elapsed_time` int(6) NOT NULL DEFAULT '0',
  `elevation` int(5) NOT NULL DEFAULT '0',
  `kcal` int(5) NOT NULL DEFAULT '0',
  `pulse_avg` int(3) NOT NULL DEFAULT '0',
  `pulse_max` int(3) NOT NULL DEFAULT '0',
  `vdot` decimal(5,2) NOT NULL DEFAULT '0.00',
  `vdot_by_time` decimal(5,2) NOT NULL DEFAULT '0.00',
  `vdot_with_elevation` decimal(5,2) NOT NULL DEFAULT '0.00',
  `use_vdot` tinyint(1) NOT NULL DEFAULT '1',
  `jd_intensity` smallint(4) NOT NULL DEFAULT '0',
  `trimp` int(4) NOT NULL DEFAULT '0',
  `cadence` int(3) NOT NULL DEFAULT '0',
  `power` int(4) NOT NULL DEFAULT '0',
  `groundcontact` smallint(5) unsigned NOT NULL DEFAULT '0',
  `vertical_oscillation` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `temperature` tinyint(4) DEFAULT NULL,
  `weatherid` smallint(6) NOT NULL DEFAULT '1',
  `route` tinytext,
  `routeid` int(10) unsigned NOT NULL,
  `clothes` varchar(100) NOT NULL,
  `splits` text,
  `comment` tinytext,
  `partner` tinytext,
  `abc` smallint(1) NOT NULL DEFAULT '0',
  `shoeid` int(11) NOT NULL DEFAULT '0',
  `notes` text NOT NULL,
  `accountid` int(11) NOT NULL,
  `creator` varchar(100) NOT NULL,
  `creator_details` tinytext NOT NULL,
  `activity_id` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `accountid` (`accountid`),
  KEY `time` (`time`),
  KEY `sportid` (`sportid`),
  KEY `typeid` (`typeid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_type`
--

CREATE TABLE IF NOT EXISTS `runalyze_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `abbr` varchar(5) NOT NULL,
  `RPE` smallint(2) NOT NULL DEFAULT '2',
  `sportid` int(11) NOT NULL DEFAULT '0',
  `accountid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `accountid` (`accountid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_user`
--

CREATE TABLE IF NOT EXISTS `runalyze_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` int(11) NOT NULL,
  `weight` decimal(4,1) NOT NULL DEFAULT '0.0',
  `pulse_rest` smallint(3) NOT NULL DEFAULT '0',
  `pulse_max` smallint(3) NOT NULL DEFAULT '0',
  `fat` decimal(3,1) NOT NULL DEFAULT '0.0',
  `water` decimal(3,1) NOT NULL DEFAULT '0.0',
  `muscles` decimal(3,1) NOT NULL DEFAULT '0.0',
  `accountid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `accountid` (`accountid`),
  KEY `time` (`time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
