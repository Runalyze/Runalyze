-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 08. Sep 2015 um 16:59
-- Server Version: 5.6.21
-- PHP-Version: 5.6.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `runalyze`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_account`
--

CREATE TABLE IF NOT EXISTS `runalyze_account` (
`id` int(10) unsigned NOT NULL,
  `username` varchar(60) NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `mail` varchar(100) NOT NULL,
  `language` varchar(5) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL DEFAULT '',
  `salt` char(64) NOT NULL DEFAULT '',
  `session_id` varchar(32) DEFAULT NULL,
  `registerdate` int(11) NOT NULL DEFAULT '0',
  `lastaction` int(11) NOT NULL DEFAULT '0',
  `lastlogin` int(11) NOT NULL DEFAULT '0',
  `autologin_hash` varchar(32) NOT NULL DEFAULT '',
  `changepw_hash` varchar(32) NOT NULL DEFAULT '',
  `changepw_timelimit` int(11) NOT NULL DEFAULT '0',
  `activation_hash` varchar(32) NOT NULL DEFAULT '',
  `deletion_hash` varchar(32) NOT NULL DEFAULT '',
  `allow_mails` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Trigger `runalyze_account`
--
DROP TRIGGER IF EXISTS `del_tr_train`;
DELIMITER //
CREATE TRIGGER `del_tr_train` AFTER DELETE ON `runalyze_account`
 FOR EACH ROW BEGIN
		DELETE FROM runalyze_conf WHERE accountid = OLD.id;
		DELETE FROM runalyze_dataset WHERE accountid = OLD.id;
		DELETE FROM runalyze_plugin WHERE accountid = OLD.id;
		DELETE FROM runalyze_sport WHERE accountid = OLD.id;
		DELETE FROM runalyze_training WHERE accountid = OLD.id;
		DELETE FROM runalyze_type WHERE accountid = OLD.id;
		DELETE FROM runalyze_user WHERE accountid = OLD.id;
	END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_activity_equipment`
--

CREATE TABLE IF NOT EXISTS `runalyze_activity_equipment` (
  `activityid` int(10) unsigned NOT NULL,
  `equipmentid` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_conf`
--

CREATE TABLE IF NOT EXISTS `runalyze_conf` (
`id` int(11) NOT NULL,
  `category` varchar(32) NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` varchar(255) NOT NULL,
  `accountid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_dataset`
--

CREATE TABLE IF NOT EXISTS `runalyze_dataset` (
  `keyid` tinyint(3) unsigned NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `style` varchar(100) NOT NULL DEFAULT '',
  `position` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `accountid` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_equipment`
--

CREATE TABLE IF NOT EXISTS `runalyze_equipment` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `typeid` int(10) unsigned NOT NULL DEFAULT '0',
  `notes` tinytext NOT NULL,
  `distance` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `additional_km` int(10) unsigned NOT NULL DEFAULT '0',
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `accountid` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_equipment_sport`
--

CREATE TABLE IF NOT EXISTS `runalyze_equipment_sport` (
  `sportid` int(10) unsigned NOT NULL,
  `equipment_typeid` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_equipment_type`
--

CREATE TABLE IF NOT EXISTS `runalyze_equipment_type` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `input` tinyint(1) NOT NULL DEFAULT '0',
  `max_km` int(11) NOT NULL DEFAULT '0',
  `max_time` int(11) NOT NULL DEFAULT '0',
  `accountid` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_tag`
--

CREATE TABLE IF NOT EXISTS `runalyze_tag` (
  `id` int(10) unsigned NOT NULL,
  `tag` varchar(50) NOT NULL,
  `accountid` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_activity_tag`
--

CREATE TABLE IF NOT EXISTS `runalyze_activity_tag` (
  `activityid` int(10) unsigned NOT NULL,
  `tagid` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_hrv`
--

CREATE TABLE IF NOT EXISTS `runalyze_hrv` (
  `accountid` int(10) unsigned NOT NULL,
  `activityid` int(10) unsigned NOT NULL,
  `data` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_plugin`
--

CREATE TABLE IF NOT EXISTS `runalyze_plugin` (
`id` int(10) unsigned NOT NULL,
  `key` varchar(100) NOT NULL,
  `type` enum('panel','stat','tool') NOT NULL DEFAULT 'stat',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `order` smallint(6) NOT NULL DEFAULT '0',
  `accountid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_plugin_conf`
--

CREATE TABLE IF NOT EXISTS `runalyze_plugin_conf` (
`id` int(10) unsigned NOT NULL,
  `pluginid` int(10) unsigned NOT NULL,
  `config` varchar(100) NOT NULL,
  `value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_route`
--

CREATE TABLE IF NOT EXISTS `runalyze_route` (
`id` int(10) unsigned NOT NULL,
  `accountid` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `cities` varchar(255) NOT NULL DEFAULT '',
  `distance` decimal(6,2) unsigned NOT NULL DEFAULT '0.00',
  `elevation` smallint(5) unsigned NOT NULL DEFAULT '0',
  `elevation_up` smallint(5) unsigned NOT NULL DEFAULT '0',
  `elevation_down` smallint(5) unsigned NOT NULL DEFAULT '0',
  `geohashes` longtext,
  `elevations_original` longtext,
  `elevations_corrected` longtext,
  `elevations_source` varchar(255) NOT NULL DEFAULT '',
  `startpoint` char(10) DEFAULT NULL,
  `endpoint` char(10) DEFAULT NULL,
  `min` char(10) DEFAULT NULL,
  `max` char(10) DEFAULT NULL,
  `in_routenet` tinyint(1) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_sport`
--

CREATE TABLE IF NOT EXISTS `runalyze_sport` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `img` varchar(100) NOT NULL DEFAULT 'unknown.gif',
  `short` tinyint(1) NOT NULL DEFAULT '0',
  `kcal` smallint(4) NOT NULL DEFAULT '0',
  `HFavg` smallint(3) NOT NULL DEFAULT '120',
  `distances` tinyint(1) NOT NULL DEFAULT '1',
  `speed` varchar(10) NOT NULL DEFAULT 'min/km',
  `power` tinyint(1) NOT NULL DEFAULT '0',
  `outside` tinyint(1) NOT NULL DEFAULT '0',
  `main_equipmenttypeid` int(10) unsigned NOT NULL DEFAULT '0',
  `accountid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_swimdata`
--

CREATE TABLE IF NOT EXISTS `runalyze_swimdata` (
  `accountid` int(10) unsigned NOT NULL,
  `activityid` int(10) unsigned NOT NULL,
  `stroke` longtext,
  `stroketype` longtext,
  `pool_length` smallint(5) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_trackdata`
--

CREATE TABLE IF NOT EXISTS `runalyze_trackdata` (
  `accountid` int(10) unsigned NOT NULL,
  `activityid` int(10) unsigned NOT NULL,
  `time` longtext,
  `distance` longtext,
  `heartrate` longtext,
  `cadence` longtext,
  `power` longtext,
  `temperature` longtext,
  `groundcontact` longtext,
  `vertical_oscillation` longtext,
  `groundcontact_balance` longtext,
  `pauses` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_training`
--

CREATE TABLE IF NOT EXISTS `runalyze_training` (
`id` int(10) unsigned NOT NULL,
  `sportid` int(11) NOT NULL DEFAULT '0',
  `typeid` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  `edited` int(11) NOT NULL DEFAULT '0',
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
  `fit_vdot_estimate` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `fit_recovery_time` smallint(5) unsigned NOT NULL DEFAULT '0',
  `fit_hrv_analysis` smallint(5) unsigned NOT NULL DEFAULT '0',
  `jd_intensity` smallint(4) NOT NULL DEFAULT '0',
  `trimp` int(4) NOT NULL DEFAULT '0',
  `cadence` int(3) NOT NULL DEFAULT '0',
  `power` int(4) NOT NULL DEFAULT '0',
  `total_strokes` smallint(5) unsigned NOT NULL DEFAULT '0',
  `swolf` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `stride_length` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `groundcontact` smallint(5) unsigned NOT NULL DEFAULT '0',
  `groundcontact_balance` SMALLINT UNSIGNED NOT NULL DEFAULT  '0',
  `vertical_oscillation` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `vertical_ratio` SMALLINT UNSIGNED NOT NULL DEFAULT  '0',
  `temperature` tinyint(4) DEFAULT NULL,
  `wind_speed` tinyint(3) unsigned DEFAULT NULL,
  `wind_deg` smallint(3) unsigned DEFAULT NULL,
  `humidity` tinyint(3) unsigned DEFAULT NULL,
  `pressure` smallint(4) unsigned DEFAULT NULL,
  `is_night` tinyint(1) unsigned DEFAULT NULL,
  `weatherid` smallint(6) NOT NULL DEFAULT '1',
  `route` text,
  `routeid` int(10) unsigned NOT NULL DEFAULT '0',
  `splits` mediumtext,
  `comment` text,
  `partner` text,
  `notes` text,
  `accountid` int(11) NOT NULL,
  `creator` varchar(100) NOT NULL DEFAULT '',
  `creator_details` tinytext,
  `activity_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 PACK_KEYS=0;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_type`
--

CREATE TABLE IF NOT EXISTS `runalyze_type` (
`id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `abbr` varchar(5) NOT NULL DEFAULT '',
  `sportid` int(11) NOT NULL DEFAULT '0',
  `short` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `hr_avg` tinyint(3) unsigned NOT NULL DEFAULT '100',
  `quality_session` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `accountid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `runalyze_user`
--

CREATE TABLE IF NOT EXISTS `runalyze_user` (
`id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `weight` decimal(5,2) NOT NULL DEFAULT '0.0',
  `pulse_rest` smallint(3) NOT NULL DEFAULT '0',
  `pulse_max` smallint(3) NOT NULL DEFAULT '0',
  `fat` decimal(3,1) NOT NULL DEFAULT '0.0',
  `water` decimal(3,1) NOT NULL DEFAULT '0.0',
  `muscles` decimal(3,1) NOT NULL DEFAULT '0.0',
  `sleep_duration` smallint(3) unsigned NOT NULL DEFAULT '0',
  `notes` text,
  `accountid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `runalyze_account`
--
ALTER TABLE `runalyze_account`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `username` (`username`), ADD UNIQUE KEY `mail` (`mail`), ADD UNIQUE KEY `session_id` (`session_id`);

--
-- Indizes für die Tabelle `runalyze_activity_equipment`
--
ALTER TABLE `runalyze_activity_equipment`
 ADD PRIMARY KEY (`activityid`,`equipmentid`), ADD KEY `equipmentid` (`equipmentid`);

--
-- Indizes für die Tabelle `runalyze_conf`
--
ALTER TABLE `runalyze_conf`
 ADD PRIMARY KEY (`id`), ADD KEY `accountid` (`accountid`);

--
-- Indizes für die Tabelle `runalyze_dataset`
--
ALTER TABLE `runalyze_dataset`
 ADD PRIMARY KEY (`accountid`,`keyid`), ADD KEY `position` (`accountid`,`position`);

--
-- Indizes für die Tabelle `runalyze_equipment`
--
ALTER TABLE `runalyze_equipment`
 ADD PRIMARY KEY (`id`), ADD KEY `accountid` (`accountid`), ADD KEY `typeid` (`typeid`);

--
-- Indizes für die Tabelle `runalyze_equipment_sport`
--
ALTER TABLE `runalyze_equipment_sport`
 ADD PRIMARY KEY (`sportid`,`equipment_typeid`), ADD KEY `equipment_typeid` (`equipment_typeid`);

--
-- Indizes für die Tabelle `runalyze_equipment_type`
--
ALTER TABLE `runalyze_equipment_type`
 ADD PRIMARY KEY (`id`), ADD KEY `accountid` (`accountid`);

--
-- Indizes für die Tabelle `runalyze_tag`
--
ALTER TABLE `runalyze_tag` 
ADD PRIMARY KEY (`id`), ADD KEY `accountid` (`accountid`);

--
-- Indizes für die Tabelle `runalyze_activity_tag`
--
ALTER TABLE `runalyze_activity_tag`
ADD PRIMARY KEY (`activityid`,`tagid`), ADD KEY `tagid` (`tagid`);

--
-- Indizes für die Tabelle `runalyze_hrv`
--
ALTER TABLE `runalyze_hrv`
 ADD PRIMARY KEY (`activityid`), ADD KEY `accountid` (`accountid`);

--
-- Indizes für die Tabelle `runalyze_plugin`
--
ALTER TABLE `runalyze_plugin`
 ADD PRIMARY KEY (`id`), ADD KEY `accountid` (`accountid`);

--
-- Indizes für die Tabelle `runalyze_plugin_conf`
--
ALTER TABLE `runalyze_plugin_conf`
 ADD PRIMARY KEY (`id`), ADD KEY `pluginid` (`pluginid`);

--
-- Indizes für die Tabelle `runalyze_route`
--
ALTER TABLE `runalyze_route`
 ADD PRIMARY KEY (`id`), ADD KEY `accountid` (`accountid`);

--
-- Indizes für die Tabelle `runalyze_sport`
--
ALTER TABLE `runalyze_sport`
 ADD PRIMARY KEY (`id`), ADD KEY `accountid` (`accountid`);

--
-- Indizes für die Tabelle `runalyze_swimdata`
--
ALTER TABLE `runalyze_swimdata`
 ADD PRIMARY KEY (`activityid`), ADD KEY `accountid` (`accountid`);

--
-- Indizes für die Tabelle `runalyze_trackdata`
--
ALTER TABLE `runalyze_trackdata`
 ADD PRIMARY KEY (`activityid`), ADD KEY `accountid` (`accountid`);

--
-- Indizes für die Tabelle `runalyze_training`
--
ALTER TABLE `runalyze_training`
 ADD PRIMARY KEY (`id`), ADD KEY `time` (`accountid`,`time`), ADD KEY `sportid` (`accountid`,`sportid`), ADD KEY `typeid` (`accountid`,`typeid`);

--
-- Indizes für die Tabelle `runalyze_type`
--
ALTER TABLE `runalyze_type`
 ADD PRIMARY KEY (`id`), ADD KEY `accountid` (`accountid`);

--
-- Indizes für die Tabelle `runalyze_user`
--
ALTER TABLE `runalyze_user`
 ADD PRIMARY KEY (`id`), ADD KEY `time` (`accountid`,`time`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `runalyze_account`
--
ALTER TABLE `runalyze_account`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `runalyze_conf`
--
ALTER TABLE `runalyze_conf`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `runalyze_equipment`
--
ALTER TABLE `runalyze_equipment`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `runalyze_tag`
--
ALTER TABLE `runalyze_tag`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `runalyze_equipment_type`
--
ALTER TABLE `runalyze_equipment_type`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `runalyze_plugin`
--
ALTER TABLE `runalyze_plugin`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `runalyze_plugin_conf`
--
ALTER TABLE `runalyze_plugin_conf`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `runalyze_route`
--
ALTER TABLE `runalyze_route`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `runalyze_sport`
--
ALTER TABLE `runalyze_sport`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `runalyze_training`
--
ALTER TABLE `runalyze_training`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `runalyze_type`
--
ALTER TABLE `runalyze_type`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `runalyze_user`
--
ALTER TABLE `runalyze_user`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Constraints der exportierten Tabellen
--


--
-- Constraints der Tabelle `runalyze_activity_tagt`
--
ALTER TABLE `runalyze_activity_tag`
ADD CONSTRAINT `runalyze_activity_tag_ibfk_1` FOREIGN KEY (`tagid`) REFERENCES `runalyze_tag` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `runalyze_activity_tag_ibfk_2` FOREIGN KEY (`activityid`) REFERENCES `runalyze_training` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
--
-- Constraints der Tabelle `runalyze_tag`
--
ALTER TABLE `runalyze_tag`
ADD CONSTRAINT `runalyze_tag_ibfk_1` FOREIGN KEY (`accountid`) REFERENCES `runalyze_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
--
-- Constraints der Tabelle `runalyze_activity_equipment`
--
ALTER TABLE `runalyze_activity_equipment`
ADD CONSTRAINT `runalyze_activity_equipment_ibfk_1` FOREIGN KEY (`equipmentid`) REFERENCES `runalyze_equipment` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `runalyze_activity_equipment_ibfk_2` FOREIGN KEY (`activityid`) REFERENCES `runalyze_training` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `runalyze_equipment`
--
ALTER TABLE `runalyze_equipment`
ADD CONSTRAINT `runalyze_equipment_ibfk_1` FOREIGN KEY (`typeid`) REFERENCES `runalyze_equipment_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `runalyze_equipment_ibfk_2` FOREIGN KEY (`accountid`) REFERENCES `runalyze_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `runalyze_equipment_sport`
--
ALTER TABLE `runalyze_equipment_sport`
ADD CONSTRAINT `runalyze_equipment_sport_ibfk_1` FOREIGN KEY (`sportid`) REFERENCES `runalyze_sport` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `runalyze_equipment_sport_ibfk_2` FOREIGN KEY (`equipment_typeid`) REFERENCES `runalyze_equipment_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `runalyze_equipment_type`
--
ALTER TABLE `runalyze_equipment_type`
ADD CONSTRAINT `runalyze_equipment_type_ibfk_1` FOREIGN KEY (`accountid`) REFERENCES `runalyze_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `runalyze_hrv`
--
ALTER TABLE `runalyze_hrv`
ADD CONSTRAINT `runalyze_hrv_ibfk_1` FOREIGN KEY (`accountid`) REFERENCES `runalyze_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `runalyze_hrv_ibfk_2` FOREIGN KEY (`activityid`) REFERENCES `runalyze_training` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `runalyze_plugin_conf`
--
ALTER TABLE `runalyze_plugin_conf`
ADD CONSTRAINT `runalyze_plugin_conf_ibfk_1` FOREIGN KEY (`pluginid`) REFERENCES `runalyze_plugin` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `runalyze_route`
--
ALTER TABLE `runalyze_route`
ADD CONSTRAINT `runalyze_route_ibfk_1` FOREIGN KEY (`accountid`) REFERENCES `runalyze_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `runalyze_swimdata`
--
ALTER TABLE `runalyze_swimdata`
ADD CONSTRAINT `runalyze_swimdata_ibfk_1` FOREIGN KEY (`accountid`) REFERENCES `runalyze_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `runalyze_swimdata_ibfk_2` FOREIGN KEY (`activityid`) REFERENCES `runalyze_training` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `runalyze_trackdata`
--
ALTER TABLE `runalyze_trackdata`
ADD CONSTRAINT `runalyze_trackdata_ibfk_1` FOREIGN KEY (`accountid`) REFERENCES `runalyze_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `runalyze_trackdata_ibfk_2` FOREIGN KEY (`activityid`) REFERENCES `runalyze_training` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
