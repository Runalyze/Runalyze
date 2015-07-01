--
-- Tabellenstruktur für Tabelle `runalyze_swim`
--

CREATE TABLE IF NOT EXISTS `runalyze_swimdata` (
  `accountid` int(10) unsigned NOT NULL,
  `activityid` int(10) unsigned NOT NULL,
  `stroke` longtext,
  `stroketype` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indizes für die Tabelle `runalyze_swim`
--
ALTER TABLE `runalyze_swim`
 ADD PRIMARY KEY (`activityid`), ADD KEY `accountid` (`accountid`);

ALTER TABLE `runalyze_training` ADD `total_strokes` int(3) NOT NULL DEFAULT '0' AFTER `power`;