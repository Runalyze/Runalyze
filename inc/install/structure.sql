-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 14. Juli 2011 um 19:34
-- Server Version: 5.1.41
-- PHP-Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `runalyze`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_conf`
--

CREATE TABLE IF NOT EXISTS `runalyze_conf` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `category` tinytext NOT NULL,
  `key` varchar(100) NOT NULL,
  `type` varchar(20) NOT NULL,
  `value` text NOT NULL,
  `description` tinytext NOT NULL,
  `select_description` tinytext NOT NULL,
  UNIQUE (
    `key`
  )
) ENGINE = MYISAM ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_config`
--

CREATE TABLE IF NOT EXISTS `runalyze_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `geschlecht` varchar(1) COLLATE latin1_general_ci NOT NULL DEFAULT 'm',
  `wunschgewicht` decimal(4,1) NOT NULL DEFAULT '0.0',
  `use_schuhe` tinyint(1) NOT NULL DEFAULT '1',
  `use_kleidung` tinyint(1) NOT NULL DEFAULT '1',
  `use_temperatur` tinyint(1) NOT NULL DEFAULT '1',
  `use_wetter` tinyint(1) NOT NULL DEFAULT '1',
  `use_strecke` tinyint(1) NOT NULL DEFAULT '1',
  `use_splits` tinyint(1) NOT NULL DEFAULT '1',
  `use_puls` tinyint(1) NOT NULL DEFAULT '1',
  `use_koerperfett` tinyint(1) NOT NULL DEFAULT '0',
  `puls_mode` varchar(5) COLLATE latin1_general_ci NOT NULL DEFAULT 'bpm',
  `use_gewicht` tinyint(1) NOT NULL DEFAULT '1',
  `use_ruhepuls` tinyint(1) NOT NULL DEFAULT '1',
  `show_user` tinyint(1) NOT NULL DEFAULT '1',
  `show_prognose` tinyint(1) NOT NULL DEFAULT '1',
  `show_rechenspiele` tinyint(1) NOT NULL DEFAULT '1',
  `max_belastung` smallint(3) NOT NULL,
  `max_atl` int(4) NOT NULL DEFAULT '0',
  `max_ctl` int(4) NOT NULL DEFAULT '0',
  `max_trimp` int(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_dataset`
--

CREATE TABLE IF NOT EXISTS `runalyze_dataset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `function` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `beschreibung` text COLLATE latin1_general_ci NOT NULL,
  `distanz` tinyint(1) NOT NULL DEFAULT '0',
  `outside` tinyint(1) NOT NULL DEFAULT '0',
  `puls` tinyint(1) NOT NULL DEFAULT '0',
  `typ` tinyint(1) NOT NULL DEFAULT '0',
  `modus` tinyint(1) NOT NULL DEFAULT '0',
  `class` varchar(25) COLLATE latin1_general_ci NOT NULL,
  `style` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `position` smallint(6) NOT NULL DEFAULT '0',
  `zusammenfassung` tinyint(1) NOT NULL DEFAULT '0',
  `zf_mode` varchar(3) COLLATE latin1_general_ci NOT NULL DEFAULT 'SUM',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_kleidung`
--

CREATE TABLE IF NOT EXISTS `runalyze_kleidung` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `name_kurz` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `order` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=15 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=17 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_schuhe`
--

CREATE TABLE IF NOT EXISTS `runalyze_schuhe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `marke` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `kaufdatum` varchar(10) COLLATE latin1_general_ci NOT NULL DEFAULT '01.01.2000',
  `km` decimal(6,2) NOT NULL DEFAULT '0.00',
  `dauer` int(11) NOT NULL DEFAULT '0',
  `inuse` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_sports`
--

CREATE TABLE IF NOT EXISTS `runalyze_sports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `bild` varchar(100) COLLATE latin1_general_ci NOT NULL DEFAULT 'unknown.gif',
  `online` tinyint(1) NOT NULL DEFAULT '1',
  `short` tinyint(1) NOT NULL DEFAULT '0',
  `kalorien` smallint(4) NOT NULL DEFAULT '0',
  `HFavg` smallint(3) NOT NULL DEFAULT '120',
  `RPE` tinyint(2) NOT NULL DEFAULT '2',
  `distanztyp` tinyint(1) NOT NULL DEFAULT '1',
  `kmh` tinyint(1) NOT NULL DEFAULT '0',
  `typen` tinyint(1) NOT NULL DEFAULT '0',
  `pulstyp` tinyint(1) NOT NULL DEFAULT '0',
  `outside` tinyint(1) NOT NULL DEFAULT '0',
  `distanz` decimal(8,2) NOT NULL DEFAULT '0.00',
  `dauer` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_training`
--

CREATE TABLE IF NOT EXISTS `runalyze_training` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sportid` int(11) NOT NULL DEFAULT '0',
  `typid` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `bahn` tinyint(1) NOT NULL DEFAULT '0',
  `distanz` decimal(6,2) NOT NULL DEFAULT '0.00',
  `dauer` decimal(7,2) NOT NULL DEFAULT '0.00',
  `pace` varchar(5) COLLATE latin1_general_ci NOT NULL DEFAULT '?:??',
  `hm` int(5) NOT NULL DEFAULT '0',
  `kalorien` int(4) NOT NULL DEFAULT '0',
  `puls` int(3) NOT NULL DEFAULT '0',
  `puls_max` int(3) NOT NULL DEFAULT '0',
  `vdot` decimal(5,2) NOT NULL DEFAULT '0.00',
  `trimp` int(4) NOT NULL DEFAULT '0',
  `temperatur` float DEFAULT NULL,
  `wetterid` smallint(6) NOT NULL DEFAULT '1',
  `strecke` tinytext COLLATE latin1_general_ci,
  `kleidung` set('1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24') COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `splits` text COLLATE latin1_general_ci,
  `bemerkung` tinytext COLLATE latin1_general_ci,
  `trainingspartner` tinytext COLLATE latin1_general_ci,
  `laufabc` smallint(1) NOT NULL DEFAULT '0',
  `schuhid` int(11) NOT NULL DEFAULT '0',
  `arr_time` longtext COLLATE latin1_general_ci,
  `arr_lat` longtext COLLATE latin1_general_ci,
  `arr_lon` longtext COLLATE latin1_general_ci,
  `arr_alt` longtext COLLATE latin1_general_ci,
  `arr_dist` longtext COLLATE latin1_general_ci,
  `arr_heart` longtext COLLATE latin1_general_ci,
  `arr_pace` longtext COLLATE latin1_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci PACK_KEYS=0 AUTO_INCREMENT=1480 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_typ`
--

CREATE TABLE IF NOT EXISTS `runalyze_typ` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `abk` varchar(5) COLLATE latin1_general_ci NOT NULL,
  `RPE` smallint(2) NOT NULL DEFAULT '2',
  `splits` tinyint(1) NOT NULL DEFAULT '0',
  `count` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_user`
--

CREATE TABLE IF NOT EXISTS `runalyze_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` int(11) NOT NULL,
  `gewicht` decimal(3,1) NOT NULL DEFAULT '0.0',
  `puls_ruhe` smallint(3) NOT NULL DEFAULT '0',
  `puls_max` smallint(3) NOT NULL DEFAULT '0',
  `fett` decimal(3,1) NOT NULL DEFAULT '0.0',
  `wasser` decimal(3,1) NOT NULL DEFAULT '0.0',
  `muskeln` decimal(3,1) NOT NULL DEFAULT '0.0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=193 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_wetter`
--

CREATE TABLE IF NOT EXISTS `runalyze_wetter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `bild` varchar(100) COLLATE latin1_general_ci NOT NULL DEFAULT 'ka.gif',
  `order` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=8 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
