/* DATE UNKNOWN - as long as equipment branch is not merged into master */

ALTER TABLE `runalyze_training` CHANGE `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `runalyze_sport` CHANGE `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `runalyze_account` CHANGE `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

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

--
-- Tabellenstruktur für Tabelle `runalyze_equipment_sport`
--

CREATE TABLE IF NOT EXISTS `runalyze_equipment_sport` (
  `sportid` int(10) unsigned NOT NULL,
  `equipment_typeid` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Tabellenstruktur für Tabelle `runalyze_activity_equipment`
--

CREATE TABLE IF NOT EXISTS `runalyze_activity_equipment` (
  `activityid` int(10) unsigned NOT NULL,
  `equipmentid` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indizes für die Tabelle `runalyze_equipment`
--
ALTER TABLE `runalyze_equipment`
 ADD PRIMARY KEY (`id`), ADD KEY `accountid` (`accountid`), ADD KEY `typeid` (`typeid`);

--
-- Indizes für die Tabelle `runalyze_equipment_type`
--
ALTER TABLE `runalyze_equipment_type`
 ADD PRIMARY KEY (`id`), ADD KEY `accountid` (`accountid`);

--
-- Indizes für die Tabelle `runalyze_activity_equipment`
--
ALTER TABLE `runalyze_activity_equipment`
 ADD PRIMARY KEY (`activityid`,`equipmentid`), ADD KEY `equipmentid` (`equipmentid`);

--
-- Indizes für die Tabelle `runalyze_equipment_sport`
--
ALTER TABLE `runalyze_equipment_sport`
 ADD PRIMARY KEY (`sportid`,`equipment_typeid`), ADD KEY `equipment_typeid` (`equipment_typeid`);

--
-- AUTO_INCREMENT für Tabelle `runalyze_equipment`
--
ALTER TABLE `runalyze_equipment`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `runalyze_equipment_type`
--
ALTER TABLE `runalyze_equipment_type`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- Constraints der exportierten Tabellen
--

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


DELETE FROM runalyze_plugin_conf where `config` = 'for_clothes' OR `config` = 'for_weather';
DELETE FROM runalyze_dataset WHERE `name` = 'shoeid' OR `name` = 'clothes';

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
