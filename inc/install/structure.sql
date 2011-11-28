-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 05. August 2011 um 22:03
-- Server Version: 5.1.41
-- PHP-Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `runalyze_empty`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_clothes`
--

CREATE TABLE IF NOT EXISTS `runalyze_clothes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `short` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `order` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=17 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_conf`
--

CREATE TABLE IF NOT EXISTS `runalyze_conf` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` tinytext COLLATE latin1_general_ci NOT NULL,
  `key` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `type` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `value` text COLLATE latin1_general_ci NOT NULL,
  `description` tinytext COLLATE latin1_general_ci NOT NULL,
  `select_description` tinytext COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_dataset`
--

CREATE TABLE IF NOT EXISTS `runalyze_dataset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `description` text COLLATE latin1_general_ci NOT NULL,
  `distance` tinyint(1) NOT NULL DEFAULT '0',
  `outside` tinyint(1) NOT NULL DEFAULT '0',
  `pulse` tinyint(1) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `modus` tinyint(1) NOT NULL DEFAULT '0',
  `class` varchar(25) COLLATE latin1_general_ci NOT NULL,
  `style` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `position` smallint(6) NOT NULL DEFAULT '0',
  `summary` tinyint(1) NOT NULL DEFAULT '0',
  `summary_mode` varchar(3) COLLATE latin1_general_ci NOT NULL DEFAULT 'SUM',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_plugin`
--

CREATE TABLE IF NOT EXISTS `runalyze_plugin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `type` enum('panel','stat','draw','tool') COLLATE latin1_general_ci NOT NULL,
  `filename` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `name` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `description` text COLLATE latin1_general_ci NOT NULL,
  `config` text COLLATE latin1_general_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `order` smallint(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_shoe`
--

CREATE TABLE IF NOT EXISTS `runalyze_shoe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `brand` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `since` varchar(10) COLLATE latin1_general_ci NOT NULL DEFAULT '01.01.2000',
  `km` decimal(6,2) NOT NULL DEFAULT '0.00',
  `time` int(11) NOT NULL DEFAULT '0',
  `inuse` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_sport`
--

CREATE TABLE IF NOT EXISTS `runalyze_sport` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `img` varchar(100) COLLATE latin1_general_ci NOT NULL DEFAULT 'unknown.gif',
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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_training`
--

CREATE TABLE IF NOT EXISTS `runalyze_training` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sportid` int(11) NOT NULL DEFAULT '0',
  `typeid` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `is_track` tinyint(1) NOT NULL DEFAULT '0',
  `distance` decimal(6,2) NOT NULL DEFAULT '0.00',
  `s` decimal(7,2) NOT NULL DEFAULT '0.00',
  `pace` varchar(5) COLLATE latin1_general_ci NOT NULL DEFAULT '?:??',
  `elevation` int(5) NOT NULL DEFAULT '0',
  `kcal` int(4) NOT NULL DEFAULT '0',
  `pulse_avg` int(3) NOT NULL DEFAULT '0',
  `pulse_max` int(3) NOT NULL DEFAULT '0',
  `vdot` decimal(5,2) NOT NULL DEFAULT '0.00',
  `trimp` int(4) NOT NULL DEFAULT '0',
  `temperature` float DEFAULT NULL,
  `weatherid` smallint(6) NOT NULL DEFAULT '1',
  `route` tinytext COLLATE latin1_general_ci,
  `clothes` set('1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24') COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `splits` text COLLATE latin1_general_ci,
  `comment` tinytext COLLATE latin1_general_ci,
  `partner` tinytext COLLATE latin1_general_ci,
  `abc` smallint(1) NOT NULL DEFAULT '0',
  `shoeid` int(11) NOT NULL DEFAULT '0',
  `arr_time` longtext COLLATE latin1_general_ci,
  `arr_lat` longtext COLLATE latin1_general_ci,
  `arr_lon` longtext COLLATE latin1_general_ci,
  `arr_alt` longtext COLLATE latin1_general_ci,
  `arr_dist` longtext COLLATE latin1_general_ci,
  `arr_heart` longtext COLLATE latin1_general_ci,
  `arr_pace` longtext COLLATE latin1_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci PACK_KEYS=0 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_type`
--

CREATE TABLE IF NOT EXISTS `runalyze_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `abbr` varchar(5) COLLATE latin1_general_ci NOT NULL,
  `RPE` smallint(2) NOT NULL DEFAULT '2',
  `splits` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_user`
--

CREATE TABLE IF NOT EXISTS `runalyze_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` int(11) NOT NULL,
  `weight` decimal(3,1) NOT NULL DEFAULT '0.0',
  `pulse_rest` smallint(3) NOT NULL DEFAULT '0',
  `pulse_max` smallint(3) NOT NULL DEFAULT '0',
  `fat` decimal(3,1) NOT NULL DEFAULT '0.0',
  `water` decimal(3,1) NOT NULL DEFAULT '0.0',
  `muscles` decimal(3,1) NOT NULL DEFAULT '0.0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_weather`
--

CREATE TABLE IF NOT EXISTS `runalyze_weather` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `img` varchar(100) COLLATE latin1_general_ci NOT NULL DEFAULT 'ka.gif',
  `order` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=8 ;
