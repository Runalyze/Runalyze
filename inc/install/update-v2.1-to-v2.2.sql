/* 08.07.2015 - add hrv table */
CREATE TABLE IF NOT EXISTS `runalyze_hrv` ( `accountid` int(10) unsigned NOT NULL, `activityid` int(10) unsigned NOT NULL, `data` longtext ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `runalyze_hrv` ADD PRIMARY KEY (`activityid`), ADD KEY `accountid` (`accountid`);

/* 16.07.2015 - on branch feature/removePaceArray - remove pace from db */
ALTER TABLE `runalyze_trackdata` DROP `pace`;

/* 01.08.2015 - add swim table */
--
-- Tabellenstruktur für Tabelle `runalyze_swim`
--

CREATE TABLE IF NOT EXISTS `runalyze_swimdata` (
  `accountid` int(10) unsigned NOT NULL,
  `activityid` int(10) unsigned NOT NULL,
  `stroke` longtext,
  `stroketype` longtext,
  `pool_length` smallint(5) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indizes für die Tabelle `runalyze_swimdata`
--
ALTER TABLE `runalyze_swimdata`
 ADD PRIMARY KEY (`activityid`), ADD KEY `accountid` (`accountid`);

ALTER TABLE `runalyze_training` ADD `total_strokes` smallint(5) unsigned NOT NULL DEFAULT '0', ADD `swolf` tinyint(3) unsigned NOT NULL DEFAULT '0' AFTER `power`;
>>>>>>> master
