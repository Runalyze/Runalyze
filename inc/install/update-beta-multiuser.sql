-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 30. März 2012 um 10:46
-- Server Version: 5.5.8
-- PHP-Version: 5.3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `run_svn_multi`
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
  `session_id` int(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `mail` (`mail`),
  UNIQUE KEY `session_id` (`session_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Daten für Tabelle `runalyze_account`
--

INSERT INTO `runalyze_account` (`id`, `username`, `name`, `mail`, `password`, `session_id`) VALUES
(1, 'mipapo', 'Michael Pohl', 'michael@mipapo.de', '', 325),
(2, 'test', 'test', 'test@test.de', 'test', NULL);

ALTER TABLE  `runalyze_clothes` ADD  `accountid` INT( 11 ) NOT NULL;
ALTER TABLE  `runalyze_conf` ADD  `accountid` INT( 11 ) NOT NULL;
ALTER TABLE  `runalyze_dataset` ADD  `accountid` INT( 11 ) NOT NULL;
ALTER TABLE  `runalyze_plugin` ADD  `accountid` INT( 11 ) NOT NULL;
ALTER TABLE  `runalyze_shoe` ADD  `accountid` INT( 11 ) NOT NULL;
ALTER TABLE  `runalyze_sport` ADD  `accountid` INT( 11 ) NOT NULL;
ALTER TABLE  `runalyze_training` ADD  `accountid` INT( 11 ) NOT NULL;
ALTER TABLE  `runalyze_type` ADD  `accountid` INT( 11 ) NOT NULL;
ALTER TABLE  `runalyze_user` ADD  `accountid` INT( 11 ) NOT NULL;
ALTER TABLE  `runalyze_weather` ADD  `accountid` INT( 11 ) NOT NULL;

UPDATE runalyze_clothes SET `accountid` = 1;
UPDATE runalyze_conf SET `accountid` = 1;
UPDATE runalyze_dataset SET `accountid` = 1;
UPDATE runalyze_plugin SET `accountid` = 1;
UPDATE runalyze_shoe SET `accountid` = 1;
UPDATE runalyze_sport SET `accountid` = 1;
UPDATE runalyze_training SET `accountid` = 1;
UPDATE runalyze_type SET `accountid` = 1;
UPDATE runalyze_user SET `accountid` = 1;
UPDATE runalyze_weather SET `accountid` = 1;
ALTER TABLE  `runalyze_conf` DROP INDEX  `key`;

