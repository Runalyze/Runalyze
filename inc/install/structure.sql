-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 25. April 2012 um 20:01
-- Server Version: 5.1.41
-- PHP-Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `runalyze`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_account`
--

CREATE TABLE `runalyze_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(60) NOT NULL,
  `name` varchar(50) NOT NULL,
  `mail` varchar(100) NOT NULL,
  `password` varchar(64) NOT NULL,
  `session_id` varchar(32) DEFAULT NULL,
  `registerdate` int(11) NOT NULL,
  `lastaction` int(11) NOT NULL,
  `lastlogin` int(11) NOT NULL,
  `autologin_hash` varchar(32) NOT NULL,
  `changepw_hash` varchar(32) NOT NULL,
  `changepw_timelimit` int(11) NOT NULL,
  `activation_hash` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `mail` (`mail`),
  UNIQUE KEY `session_id` (`session_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_clothes`
--

CREATE TABLE `runalyze_clothes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `short` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `order` tinyint(1) NOT NULL,
  `accountid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_conf`
--

CREATE TABLE `runalyze_conf` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `value` text CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `accountid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_dataset`
--

CREATE TABLE `runalyze_dataset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `label` varchar(100) NOT NULL,
  `description` text CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `distance` tinyint(1) NOT NULL DEFAULT '0',
  `outside` tinyint(1) NOT NULL DEFAULT '0',
  `pulse` tinyint(1) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `modus` tinyint(1) NOT NULL DEFAULT '0',
  `class` varchar(25) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `style` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `position` smallint(6) NOT NULL DEFAULT '0',
  `summary` tinyint(1) NOT NULL DEFAULT '0',
  `summary_mode` varchar(3) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT 'SUM',
  `accountid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_plugin`
--

CREATE TABLE `runalyze_plugin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `type` enum('panel','stat','draw','tool') CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `filename` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `name` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `description` text CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `config` text CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `internal_data` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `order` smallint(6) NOT NULL,
  `accountid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_shoe`
--

CREATE TABLE `runalyze_shoe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `brand` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `since` varchar(10) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '01.01.2000',
  `km` decimal(6,2) NOT NULL DEFAULT '0.00',
  `time` int(11) NOT NULL DEFAULT '0',
  `inuse` tinyint(1) NOT NULL DEFAULT '1',
  `additionalKm` decimal(6,2) NOT NULL DEFAULT '0.00',
  `accountid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_sport`
--

CREATE TABLE `runalyze_sport` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `img` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT 'unknown.gif',
  `online` tinyint(1) NOT NULL DEFAULT '1',
  `short` tinyint(1) NOT NULL DEFAULT '0',
  `kcal` smallint(4) NOT NULL DEFAULT '0',
  `HFavg` smallint(3) NOT NULL DEFAULT '120',
  `RPE` tinyint(2) NOT NULL DEFAULT '2',
  `distances` tinyint(1) NOT NULL DEFAULT '1',
  `kmh` tinyint(1) NOT NULL DEFAULT '0',
  `types` tinyint(1) NOT NULL DEFAULT '0',
  `pulse` tinyint(1) NOT NULL DEFAULT '0',
  `outside` tinyint(1) NOT NULL DEFAULT '0',
  `accountid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_training`
--

CREATE TABLE `runalyze_training` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sportid` int(11) NOT NULL DEFAULT '0',
  `typeid` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `is_track` tinyint(1) NOT NULL DEFAULT '0',
  `distance` decimal(6,2) NOT NULL DEFAULT '0.00',
  `s` decimal(7,2) NOT NULL DEFAULT '0.00',
  `pace` varchar(5) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '?:??',
  `elevation` int(5) NOT NULL DEFAULT '0',
  `kcal` int(4) NOT NULL DEFAULT '0',
  `pulse_avg` int(3) NOT NULL DEFAULT '0',
  `pulse_max` int(3) NOT NULL DEFAULT '0',
  `vdot` decimal(5,2) NOT NULL DEFAULT '0.00',
  `trimp` int(4) NOT NULL DEFAULT '0',
  `temperature` float DEFAULT NULL,
  `weatherid` smallint(6) NOT NULL DEFAULT '1',
  `route` tinytext CHARACTER SET latin1 COLLATE latin1_general_ci,
  `clothes` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `splits` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `comment` tinytext CHARACTER SET latin1 COLLATE latin1_general_ci,
  `partner` tinytext CHARACTER SET latin1 COLLATE latin1_general_ci,
  `abc` smallint(1) NOT NULL DEFAULT '0',
  `shoeid` int(11) NOT NULL DEFAULT '0',
  `notes` text NOT NULL,
  `arr_time` longtext CHARACTER SET latin1 COLLATE latin1_general_ci,
  `arr_lat` longtext CHARACTER SET latin1 COLLATE latin1_general_ci,
  `arr_lon` longtext CHARACTER SET latin1 COLLATE latin1_general_ci,
  `arr_alt` longtext CHARACTER SET latin1 COLLATE latin1_general_ci,
  `arr_dist` longtext CHARACTER SET latin1 COLLATE latin1_general_ci,
  `arr_heart` longtext CHARACTER SET latin1 COLLATE latin1_general_ci,
  `arr_pace` longtext CHARACTER SET latin1 COLLATE latin1_general_ci,
  `accountid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_type`
--

CREATE TABLE `runalyze_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `abbr` varchar(5) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `RPE` smallint(2) NOT NULL DEFAULT '2',
  `splits` tinyint(1) NOT NULL DEFAULT '0',
  `accountid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_user`
--

CREATE TABLE `runalyze_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` int(11) NOT NULL,
  `weight` decimal(4,1) NOT NULL DEFAULT '0.0',
  `pulse_rest` smallint(3) NOT NULL DEFAULT '0',
  `pulse_max` smallint(3) NOT NULL DEFAULT '0',
  `fat` decimal(3,1) NOT NULL DEFAULT '0.0',
  `water` decimal(3,1) NOT NULL DEFAULT '0.0',
  `muscles` decimal(3,1) NOT NULL DEFAULT '0.0',
  `accountid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
