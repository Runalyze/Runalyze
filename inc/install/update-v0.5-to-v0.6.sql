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

-- Add enum 'tool' for plugins
--

ALTER TABLE `runalyze_plugin` CHANGE `type` `type` ENUM( 'panel', 'stat', 'draw', 'tool' ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL ;




RENAME TABLE `runalyze_kleidung` TO `runalyze_clothes` ;
ALTER TABLE `runalyze_clothes` CHANGE `name_kurz` `short` VARCHAR( 20 ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL ;

--

ALTER TABLE `runalyze_training` CHANGE `typid` `typeid` INT( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE `runalyze_training` CHANGE `bahn` `is_track` TINYINT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `runalyze_training` CHANGE `distanz` `distance` DECIMAL( 6, 2 ) NOT NULL DEFAULT '0.00';
ALTER TABLE `runalyze_training` CHANGE `dauer` `s` DECIMAL( 7, 2 ) NOT NULL DEFAULT '0.00';
ALTER TABLE `runalyze_training` CHANGE `hm` `elevation` INT( 5 ) NOT NULL DEFAULT '0';
ALTER TABLE `runalyze_training` CHANGE `kalorien` `kcal` INT( 4 ) NOT NULL DEFAULT '0';
ALTER TABLE `runalyze_training` CHANGE `puls` `pulse_avg` INT( 3 ) NOT NULL DEFAULT '0';
ALTER TABLE `runalyze_training` CHANGE `puls_max` `pulse_max` INT( 3 ) NOT NULL DEFAULT '0';
ALTER TABLE `runalyze_training` CHANGE `schuhid` `shoeid` INT( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE `runalyze_training` CHANGE `temperatur` `temperature` FLOAT NULL DEFAULT NULL ;
ALTER TABLE `runalyze_training` CHANGE `wetterid` `weatherid` SMALLINT( 6 ) NOT NULL DEFAULT '1';
ALTER TABLE `runalyze_training` CHANGE `strecke` `route` TINYTEXT CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL ;
ALTER TABLE `runalyze_training` CHANGE `kleidung` `clothes` SET( '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24' ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '';
ALTER TABLE `runalyze_training` CHANGE `bemerkung` `comment` TINYTEXT CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL ;
ALTER TABLE `runalyze_training` CHANGE `trainingspartner` `partner` TINYTEXT CHARACTER SET latin1 COLLATE latin1_general_ci NULL DEFAULT NULL ;
ALTER TABLE `runalyze_training` CHANGE `laufabc` `abc` SMALLINT( 1 ) NOT NULL DEFAULT '0';

--

RENAME TABLE `runalyze_wetter` TO `runalyze_weather` ;
ALTER TABLE `runalyze_weather` CHANGE `bild` `img` VARCHAR( 100 ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT 'ka.gif';

--

ALTER TABLE `runalyze_user` CHANGE `gewicht` `weight` DECIMAL( 3, 1 ) NOT NULL DEFAULT '0.0';
ALTER TABLE `runalyze_user` CHANGE `puls_ruhe` `pulse_rest` SMALLINT( 3 ) NOT NULL DEFAULT '0';
ALTER TABLE `runalyze_user` CHANGE `puls_max` `pulse_max` SMALLINT( 3 ) NOT NULL DEFAULT '0';
ALTER TABLE `runalyze_user` CHANGE `fett` `fat` DECIMAL( 3, 1 ) NOT NULL DEFAULT '0.0';
ALTER TABLE `runalyze_user` CHANGE `wasser` `water` DECIMAL( 3, 1 ) NOT NULL DEFAULT '0.0';
ALTER TABLE `runalyze_user` CHANGE `muskeln` `muscles` DECIMAL( 3, 1 ) NOT NULL DEFAULT '0.0';

--

RENAME TABLE `runalyze_typ` TO `runalyze_type` ;
ALTER TABLE `runalyze_type` DROP `count`;
ALTER TABLE `runalyze_type` CHANGE `abk` `abbr` VARCHAR( 5 ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL ;

--

RENAME TABLE `runalyze_sports` TO `runalyze_sport` ;
ALTER TABLE `runalyze_sport` CHANGE `bild` `img` VARCHAR( 100 ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT 'unknown.gif';
ALTER TABLE `runalyze_sport` CHANGE `kalorien` `kcal` SMALLINT( 4 ) NOT NULL DEFAULT '0';
ALTER TABLE `runalyze_sport` CHANGE `distanztyp` `distances` TINYINT( 1 ) NOT NULL DEFAULT '1';
ALTER TABLE `runalyze_sport` CHANGE `typen` `types` TINYINT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `runalyze_sport` CHANGE `pulstyp` `pulse` TINYINT( 1 ) NOT NULL DEFAULT '0';

--

RENAME TABLE `runalyze_schuhe` TO `runalyze_shoe` ;
ALTER TABLE `runalyze_shoe` CHANGE `marke` `brand` VARCHAR( 20 ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL ;
ALTER TABLE `runalyze_shoe` CHANGE `kaufdatum` `since` VARCHAR( 10 ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '01.01.2000';
ALTER TABLE `runalyze_shoe` CHANGE `dauer` `time` INT( 11 ) NOT NULL DEFAULT '0';

--

ALTER TABLE `runalyze_sport` DROP `distanz`, DROP `dauer`;
  
--

DROP TABLE `runalyze_config`;

--

TRUNCATE TABLE `runalyze_plugin`;

INSERT INTO `runalyze_plugin` (`id`, `key`, `type`, `filename`, `name`, `description`, `config`, `active`, `order`) VALUES
(1, 'RunalyzePluginPanel_Sports', 'panel', 'panel.sports.inc.php', 'Sportarten', '&Uuml;bersicht der Leistungen aller Sportarten für den aktuellen Monat, das Jahr oder seit Anfang der Aufzeichnung.', '', 1, 1),
(2, 'RunalyzePluginPanel_Rechenspiele', 'panel', 'panel.rechenspiele.inc.php', 'Rechenspiele', 'Anzeige der Rechenspiele M&uuml;digkeit, Grundlagenausdauer und Trainingsform.', '', 1, 2),
(3, 'RunalyzePluginPanel_Prognose', 'panel', 'panel.prognose.inc.php', 'Prognose', 'Anzeige der aktuellen Wettkampfprognose.', 'distances|array=1, 3, 5, 10, 21.1, 42.2|Distanzen f&uuml;r die Prognose (kommagetrennt)', 2, 3),
(4, 'RunalyzePluginPanel_Schuhe', 'panel', 'panel.schuhe.inc.php', 'Schuhe', 'Anzeige der bisher gelaufenen Kilometer mit den aktiven Schuhen, bei Bedarf auch der alten Schuhe.', '', 2, 4),
(5, 'RunalyzePluginPanel_Sportler', 'panel', 'panel.sportler.inc.php', 'Sportler', 'Anzeige der Sportlerdaten wie Gewicht und aktueller Ruhepuls (auch als Diagramm).', 'use_weight|bool=true|Gewicht protokollieren\r\nuse_body_fat|bool=true|Fettanteil protokollieren\r\nuse_pulse|bool=true|Ruhepuls protokollieren\r\nwunschgewicht|int=68.0|Wunschgewicht', 1, 5),
(6, 'RunalyzePluginStat_Schuhe', 'stat', 'stat.schuhe.inc.php', 'Schuhe', 'Ausf&uuml;hrliche Statistiken zu den Schuhen: Durchschnittliche, maximale und absolute Leistung (Kilometer / Tempo).', '', 1, 4),
(7, 'RunalyzePluginStat_Analyse', 'stat', 'stat.analyse.inc.php', 'Analyse', 'Analyse des Trainings zum Tempo, der Distanz und den verschiedenen Trainingstypen.', 'use_type|bool=true|Trainingstypen analysieren\r\nuse_pace|bool=true|Tempobereiche analysieren\r\nuse_pulse|bool=true|Pulsbereiche analysieren\r\nlowest_pulsegroup|int=65|Niedrigster Pulsbereich (%HFmax)\r\npulsegroup_step|int=5|Pulsbereich: Schrittweite\r\nlowest_pacegroup|int=360|Niedrigster Tempobereich (s/km)\r\nhighest_pacegroup|int=210|H&ouml;chster Tempobereich (s/km)\r\npacegroup_step|int=15|Tempobereich: Schrittweite', 1, 2),
(8, 'RunalyzePluginStat_Statistiken', 'stat', 'stat.statistiken.inc.php', 'Statistiken', 'Allgemeine Statistiken: Monatszusammenfassung in der Jahres&uuml;bersicht für alle Sportarten.', '', 1, 1),
(9, 'RunalyzePluginStat_Wettkampf', 'stat', 'stat.wettkampf.inc.php', 'Wettk&auml;mpfe', 'Bestzeiten und alles weitere zu den bisher gelaufenen Wettk&auml;mpfen.', 'last_wk_num|int=10|Anzahl f&uuml;r letzte Wettk&auml;mpfe\r\nmain_distance|int=10|Hauptdistanz (wird als Diagramm dargestellt)\r\npb_distances|array=1, 3, 5, 10, 21.1, 42.2|Distanzen f&uuml;r Bestzeit-Vergleich (kommagetrennt)', 1, 3),
(10, 'RunalyzePluginStat_Wetter', 'stat', 'stat.wetter.inc.php', 'Wetter', 'Wetterverh&auml;ltnisse, Temperaturen und die getragenen Kleidungsst&uuml;cke.', 'for_weather|bool=true|Wetter-Statistiken anzeigen\r\nfor_clothes|bool=true|Kleidung-Statistiken anezigen', 1, 5),
(11, 'RunalyzePluginStat_Rekorde', 'stat', 'stat.rekorde.inc.php', 'Rekorde', 'Am schnellsten, am l&auml;ngsten, am weitesten: Die Rekorde aus dem Training.', '', 2, 6),
(12, 'RunalyzePluginStat_Strecken', 'stat', 'stat.strecken.inc.php', 'Strecken', 'Auflistung der h&auml;ufigsten und seltensten Strecken/Orte.', '', 2, 7),
(13, 'RunalyzePluginStat_Trainingszeiten', 'stat', 'stat.trainingszeiten.inc.php', 'Trainingszeiten', 'Auflistung n&auml;chtlicher Trainings und Diagramme &uuml;ber die Trainingszeiten.', '', 2, 8),
(14, 'RunalyzePluginStat_Trainingspartner', 'stat', 'stat.trainingspartner.inc.php', 'Trainingspartner', 'Wie oft hast du mit wem gemeinsam trainiert?', '', 2, 9),
(15, 'RunalyzePluginStat_Hoehenmeter', 'stat', 'stat.hoehenmeter.inc.php', 'H&ouml;henmeter', 'Die steilsten und bergigsten L&auml;ufe sowie der &Uuml;berblick &uuml;ber die absolvierten H&ouml;henmeter aller Monate.', '', 2, 10),
(16, 'RunalyzePluginStat_Laufabc', 'stat', 'stat.laufabc.inc.php', 'Lauf-ABC', 'Wie oft hast du Lauf-ABC absolviert?', '', 2, 11),
(17, 'RunalyzePluginTool_Cacheclean', 'tool', 'class.RunalyzePlugin_CachecleanTool.php', 'Cacheclean', 'L&ouml;scht den Cache der Diagramme. Sollte genutzt werden, falls Probleme mit Diagrammen auftauchen.', '', 1, 99),
(18, 'RunalyzePluginTool_DatenbankCleanup', 'tool', 'class.RunalyzePlugin_DatenbankCleanupTool.php', 'Datenbank-Cleanup', 'Reinigt die Datenbank. Dies ist unter Umst&auml;nden nach dem L&ouml;schen von Trainings notwendig.', '', 1, 99);

--

ALTER TABLE `runalyze_dataset` CHANGE `beschreibung` `description` TEXT CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL ;
ALTER TABLE `runalyze_dataset` CHANGE `distanz` `distance` TINYINT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `runalyze_dataset` CHANGE `puls` `pulse` TINYINT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `runalyze_dataset` CHANGE `typ` `type` TINYINT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `runalyze_dataset` CHANGE `zusammenfassung` `summary` TINYINT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `runalyze_dataset` CHANGE `zf_mode` `summary_mode` VARCHAR( 3 ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT 'SUM';

TRUNCATE TABLE `runalyze_dataset`;
TRUNCATE TABLE `runalyze_conf`;

INSERT INTO `runalyze_dataset` (`id`, `name`, `description`, `distance`, `outside`, `pulse`, `type`, `modus`, `class`, `style`, `position`, `summary`, `summary_mode`) VALUES
(1, 'sportid', 'Anzeige des Symbols der jeweiligen Sportart, welches mit dem Informationsfenster mit allen Daten verknüpft ist.', 0, 0, 0, 0, 3, '', '', 4, 0, 'YES'),
(2, 'typeid', 'Anzeige der Abkürzung für den Trainingstyp wie Intervalltraining (IT) oder Dauerlauf (DL).', 0, 0, 0, 1, 2, '', '', 3, 0, 'NO'),
(3, 'time', 'Anzeige der Uhrzeit. Datum und Wochentag werden automatisch angezeigt.', 0, 0, 0, 0, 1, 'c', '', 0, 0, 'NO'),
(4, 'distance', 'Anzeige der Distanz in Kilometern, bei Bahn-Angaben in Metern.', 1, 0, 0, 0, 2, '', '', 5, 1, 'SUM'),
(5, 's', 'Anzeige der Trainingsdauer.', 0, 0, 0, 0, 3, '', '', 6, 1, 'SUM'),
(6, 'pace', 'Anzeige des Tempos, je nach Sportart in km/h oder min/km.', 1, 0, 0, 0, 2, 'small', '', 7, 1, 'AVG'),
(7, 'elevation', 'Anzeige der bewältigten Höhenmeter.', 1, 1, 0, 0, 2, 'small', '', 9, 1, 'SUM'),
(8, 'kcal', 'Anzeige der (vermutlich) verbrauchten Kalorien.', 0, 0, 0, 0, 2, 'small', '', 10, 1, 'SUM'),
(9, 'pulse_avg', 'Anzeige des durchschnittlichen Pulses je nach Einstellung als absoluter Wert oder als Prozent der maximalen Herzfrequenz.', 0, 0, 1, 0, 2, 'small', 'font-style:italic;', 8, 1, 'AVG'),
(10, 'pulse_max', 'Anzeige des maximalen Pulses beim Training.', 0, 0, 1, 0, 1, 'small', '', 0, 0, 'MAX'),
(11, 'trimp', 'Anzeige des Belastungswertes "TRainingsIMPulse".', 0, 0, 0, 0, 2, '', '', 13, 1, 'SUM'),
(12, 'temperature', 'Anzeige der Temperatur', 0, 1, 0, 0, 2, 'small', 'width:35px;', 2, 0, 'AVG'),
(13, 'weatherid', 'Anzeige des Wettersymbols', 0, 1, 0, 0, 2, '', '', 1, 0, 'NO'),
(14, 'route', 'Anzeige des Streckenverlaufs', 1, 1, 0, 0, 1, 'small l', '', 18, 0, 'NO'),
(15, 'clothes', 'Anzeige der benutzten Kleidung.', 0, 1, 0, 0, 1, 'small l', '', 16, 0, 'NO'),
(16, 'splits', 'Anzeige der Splits beim Intervalltraining oder Wettkampf.', 1, 0, 0, 1, 2, '', '', 11, 0, 'NO'),
(17, 'comment', 'Anzeige der Bemerkung (auf 25 Zeichen gekürzt) sowie ein Link zu dem Split-Diagramm, falls Splitzeiten vorhanden sind.', 0, 0, 0, 0, 2, 'small l', '', 12, 0, 'NO'),
(18, 'shoeid', 'Anzeige des benutzten Schuhs.', 1, 1, 0, 0, 1, 'small l', '', 0, 0, 'NO'),
(19, 'vdot', 'Anzeige der aus dem Lauf (mittels der Pulsdaten) berechneten Form.', 1, 0, 1, 1, 2, '', '', 14, 1, 'AVG'),
(20, 'partner', 'Anzeige der Trainingspartner, mit denen man trainiert hat.', 0, 0, 0, 0, 1, 'small', '', 17, 0, 'NO'),
(21, 'abc', 'Anzeige eines kleinen Symbols, wenn man beim Training das Lauf-ABC absolviert hat.', 0, 0, 0, 1, 1, '', '', 15, 0, 'NO');


ALTER TABLE `runalyze_dataset` DROP `function` ;