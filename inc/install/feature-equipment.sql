--
-- Tabellenstruktur für Tabelle `runalyze_equipment_type`
--

CREATE TABLE IF NOT EXISTS `runalyze_equipment_type` (
`id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `input`  tinyint(1) NOT NULL DEFAULT '0',
  `max_km` decimal(6,2) NOT NULL DEFAULT '0.00',
  `max_time` int(11) NOT NULL DEFAULT '0',
  `accountid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Tabellenstruktur für Tabelle `runalyze_equipment`
--

CREATE TABLE IF NOT EXISTS `runalyze_equipment` (
`id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `typeid` int(11) NOT NULL DEFAULT '0',
  `notes` text,
  `distance` decimal(6,2) NOT NULL DEFAULT '0.00',
  `time` int(11) NOT NULL DEFAULT '0',
  `additional_km` decimal(6,2) NOT NULL DEFAULT '0.00',
  `date_start` int(11) NOT NULL DEFAULT '0',
  `date_end` int(11) NOT NULL DEFAULT '0',
  `accountid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Tabellenstruktur für Tabelle `runalyze_equipment_sport`
--

CREATE TABLE IF NOT EXISTS `runalyze_equipment_sport` (
`sportid` int(11) NOT NULL,
  `equipment_typeid` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Tabellenstruktur für Tabelle `runalyze_activity_equipment`
--

CREATE TABLE IF NOT EXISTS `runalyze_activity_equipment` (
`activityid` int(11) NOT NULL,
  `equipmentid` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indizes für die Tabelle `runalyze_equipment_type`
--
ALTER TABLE `runalyze_equipment`
 ADD PRIMARY KEY (`id`), ADD KEY `time` (`accountid`,`time`);
--
-- Indizes für die Tabelle `runalyze_equipment_type`
--
ALTER TABLE `runalyze_equipment_type`
 ADD PRIMARY KEY (`id`), ADD KEY `accountid` (`accountid`);
--
-- AUTO_INCREMENT für Tabelle `runalyze_equipment`
--
ALTER TABLE `runalyze_equipment`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `runalyze_equipment_type`
--
ALTER TABLE `runalyze_equipment_type`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;



DELETE FROM runalyze_plugin WHERE `key` = 'RunalyzePluginPanel_Schuhe';
DELETE FROM runalyze_plugin_conf where `config` = 'for_clothes';
DELETE FROM runalyze_dataset WHERE `name` = 'shoeid' OR `name` = 'clothes';

 
