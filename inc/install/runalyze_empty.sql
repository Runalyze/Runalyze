-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 05. August 2011 um 22:05
-- Server Version: 5.1.41
-- PHP-Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `runalyze_empty`
--

--
-- Daten f&uuml;r Tabelle `runalyze_clothes`
--

INSERT INTO `runalyze_clothes` (`id`, `name`, `short`, `order`) VALUES
(1, 'Langarmshirt', 'S-Lang', 1),
(2, 'T-Shirt', 'Shirt', 1),
(3, 'Singlet', 'Singlet', 1),
(4, 'Jacke', 'Jacke', 1),
(5, 'kurze Hose', 'H-kurz', 2),
(6, 'lange Hose', 'H-lang', 2),
(7, 'Laufshorts', 'Shorts', 2),
(8, 'Handschuhe', 'Handschuhe', 3),
(9, 'Muetze', 'Muetze', 4);

--
-- Daten f&uuml;r Tabelle `runalyze_dataset`
--

INSERT INTO `runalyze_dataset` (`id`, `name`, `label`, `description`, `distance`, `outside`, `pulse`, `type`, `modus`, `class`, `style`, `position`, `summary`, `summary_mode`) VALUES
(1, 'sportid', 'Sportart', 'Anzeige des Symbols der jeweiligen Sportart, welches mit dem Informationsfenster mit allen Daten verkn&uuml;pft ist.', 0, 0, 0, 0, 3, '', '', 4, 0, 'YES'),
(2, 'typeid', 'Trainingstyp', 'Anzeige der Abk&uuml;rzung f&uuml;r den Trainingstyp wie Intervalltraining (IT) oder Dauerlauf (DL).', 0, 0, 0, 1, 2, '', '', 3, 0, 'NO'),
(3, 'time', 'Uhrzeit', 'Anzeige der Uhrzeit. Datum und Wochentag werden automatisch angezeigt.', 0, 0, 0, 0, 1, 'c', '', 0, 0, 'NO'),
(4, 'distance', 'Distanz', 'Anzeige der Distanz in Kilometern, bei Bahn-Angaben in Metern.', 1, 0, 0, 0, 2, '', '', 5, 1, 'SUM'),
(5, 's', 'Dauer', 'Anzeige der Trainingsdauer.', 0, 0, 0, 0, 3, '', '', 6, 1, 'SUM'),
(6, 'pace', 'Pace', 'Anzeige des Tempos, je nach Sportart in km/h oder min/km.', 1, 0, 0, 0, 2, 'small', '', 7, 1, 'AVG'),
(7, 'elevation', 'H&ouml;henmeter', 'Anzeige der bew&auml;ltigten H&ouml;henmeter.', 1, 1, 0, 0, 2, 'small', '', 9, 1, 'SUM'),
(8, 'kcal', 'Kalorien', 'Anzeige der (vermutlich) verbrauchten Kalorien.', 0, 0, 0, 0, 2, 'small', '', 10, 1, 'SUM'),
(9, 'pulse_avg', 'durchschn. Puls', 'Anzeige des durchschnittlichen Pulses je nach Einstellung als absoluter Wert oder als Prozent der maximalen Herzfrequenz.', 0, 0, 1, 0, 2, 'small', 'font-style:italic;', 8, 1, 'AVG'),
(10, 'pulse_max', 'max. Puls', 'Anzeige des maximalen Pulses beim Training.', 0, 0, 1, 0, 1, 'small', '', 0, 0, 'MAX'),
(11, 'trimp', 'TRIMP', 'Anzeige des Belastungswertes "TRainingsIMPulse".', 0, 0, 0, 0, 2, '', '', 13, 1, 'SUM'),
(12, 'temperature', 'Temperatur', 'Anzeige der Temperatur', 0, 1, 0, 0, 2, 'small', 'width:35px;', 2, 0, 'AVG'),
(13, 'weatherid', 'Wetter', 'Anzeige des Wettersymbols', 0, 1, 0, 0, 2, '', '', 1, 0, 'NO'),
(14, 'route', 'Strecke', 'Anzeige des Streckenverlaufs', 1, 1, 0, 0, 1, 'small l', '', 18, 0, 'NO'),
(15, 'clothes', 'Kleidung', 'Anzeige der benutzten Kleidung.', 0, 1, 0, 0, 1, 'small l', '', 16, 0, 'NO'),
(16, 'splits', 'Zwischenzeiten', 'Anzeige der Splits beim Intervalltraining oder Wettkampf.', 1, 0, 0, 1, 2, '', '', 11, 0, 'NO'),
(17, 'comment', 'Bemerkung', 'Anzeige der Bemerkung (auf 25 Zeichen gek&uuml;rzt) sowie ein Link zu dem Split-Diagramm, falls Splitzeiten vorhanden sind.', 0, 0, 0, 0, 2, 'small l', '', 12, 0, 'NO'),
(18, 'shoeid', 'Schuh', 'Anzeige des benutzten Schuhs.', 1, 1, 0, 0, 1, 'small l', '', 0, 0, 'NO'),
(19, 'vdot', 'VDOT', 'Anzeige der aus dem Lauf (mittels der Pulsdaten) berechneten Form.', 1, 0, 1, 1, 2, '', '', 14, 1, 'AVG'),
(20, 'partner', 'Trainingspartner', 'Anzeige der Trainingspartner, mit denen man trainiert hat.', 0, 0, 0, 0, 1, 'small', '', 17, 0, 'NO'),
(21, 'abc', 'Lauf-ABC', 'Anzeige eines kleinen Symbols, wenn man beim Training das Lauf-ABC absolviert hat.', 0, 0, 0, 1, 1, '', '', 15, 0, 'NO');

--
-- Daten f&uuml;r Tabelle `runalyze_plugin`
--

INSERT INTO `runalyze_plugin` (`id`, `key`, `type`, `filename`, `name`, `description`, `config`, `active`, `order`) VALUES
(1, 'RunalyzePluginPanel_Sports', 'panel', 'panel.sports.inc.php', 'Sportarten', '&Uuml;bersicht der Leistungen aller Sportarten f&uuml;r den aktuellen Monat, das Jahr oder seit Anfang der Aufzeichnung.', '', 1, 1),
(2, 'RunalyzePluginPanel_Rechenspiele', 'panel', 'panel.rechenspiele.inc.php', 'Rechenspiele', 'Anzeige der Rechenspiele M&uuml;digkeit, Grundlagenausdauer und Trainingsform.', 'show_trainingpaces|bool=true|Empfohlene Trainingstempi anzeigen\n', 1, 2),
(3, 'RunalyzePluginPanel_Prognose', 'panel', 'panel.prognose.inc.php', 'Prognose', 'Anzeige der aktuellen Wettkampfprognose.', 'distances|array=1, 3, 5, 10, 21.1, 42.2|Distanzen f&uuml;r die Prognose (kommagetrennt)', 2, 3),
(4, 'RunalyzePluginPanel_Schuhe', 'panel', 'panel.schuhe.inc.php', 'Schuhe', 'Anzeige der bisher gelaufenen Kilometer mit den aktiven Schuhen, bei Bedarf auch der alten Schuhe.', '', 2, 4),
(5, 'RunalyzePluginPanel_Sportler', 'panel', 'panel.sportler.inc.php', 'Sportler', 'Anzeige der Sportlerdaten wie Gewicht und aktueller Ruhepuls (auch als Diagramm).', 'use_weight|bool=true|Gewicht protokollieren\nuse_body_fat|bool=true|Fettanteil protokollieren\nuse_pulse|bool=true|Ruhepuls protokollieren\nwunschgewicht|int=66.0|Wunschgewicht\n', 1, 5),
(6, 'RunalyzePluginStat_Schuhe', 'stat', 'stat.schuhe.inc.php', 'Schuhe', 'Ausf&uuml;hrliche Statistiken zu den Schuhen: Durchschnittliche, maximale und absolute Leistung (Kilometer / Tempo).', '', 1, 4),
(7, 'RunalyzePluginStat_Analyse', 'stat', 'stat.analyse.inc.php', 'Analyse', 'Analyse des Trainings zum Tempo, der Distanz und den verschiedenen Trainingstypen.', 'use_type|bool=true|Trainingstypen analysieren\r\nuse_pace|bool=true|Tempobereiche analysieren\r\nuse_pulse|bool=true|Pulsbereiche analysieren\r\nlowest_pulsegroup|int=65|Niedrigster Pulsbereich (%HFmax)\r\npulsegroup_step|int=5|Pulsbereich: Schrittweite\r\nlowest_pacegroup|int=360|Niedrigster Tempobereich (s/km)\r\nhighest_pacegroup|int=210|H&ouml;chster Tempobereich (s/km)\r\npacegroup_step|int=15|Tempobereich: Schrittweite', 1, 2),
(8, 'RunalyzePluginStat_Statistiken', 'stat', 'stat.statistiken.inc.php', 'Statistiken', 'Allgemeine Statistiken: Monatszusammenfassung in der Jahres&uuml;bersicht f&uuml;r alle Sportarten.', '', 1, 1),
(9, 'RunalyzePluginStat_Wettkampf', 'stat', 'stat.wettkampf.inc.php', 'Wettk&auml;mpfe', 'Bestzeiten und alles weitere zu den bisher gelaufenen Wettk&auml;mpfen.', 'last_wk_num|int=10|Anzahl f&uuml;r letzte Wettk&auml;mpfe\nmain_distance|int=10|Hauptdistanz (wird als Diagramm dargestellt)\npb_distances|array=1,     3,     5,     10,     21.1,     42.2|Distanzen f&uuml;r Bestzeit-Vergleich (kommagetrennt)\nfun_ids|array=1453,     1248,  1078, 1252|IDs der Spa&szlig;-Wettk&auml;mpfe (nicht per Hand editieren!)\n', 1, 3),
(10, 'RunalyzePluginStat_Wetter', 'stat', 'stat.wetter.inc.php', 'Wetter', 'Wetterverh&auml;ltnisse, Temperaturen und die getragenen Kleidungsst&uuml;cke.', 'for_weather|bool=true|Wetter-Statistiken anzeigen\r\nfor_clothes|bool=true|Kleidung-Statistiken anezigen', 1, 5),
(11, 'RunalyzePluginStat_Rekorde', 'stat', 'stat.rekorde.inc.php', 'Rekorde', 'Am schnellsten, am l&auml;ngsten, am weitesten: Die Rekorde aus dem Training.', '', 2, 6),
(12, 'RunalyzePluginStat_Strecken', 'stat', 'stat.strecken.inc.php', 'Strecken', 'Auflistung der h&auml;ufigsten und seltensten Strecken/Orte.', '', 2, 7),
(13, 'RunalyzePluginStat_Trainingszeiten', 'stat', 'stat.trainingszeiten.inc.php', 'Trainingszeiten', 'Auflistung n&auml;chtlicher Trainings und Diagramme &uuml;ber die Trainingszeiten.', '', 2, 8),
(14, 'RunalyzePluginStat_Trainingspartner', 'stat', 'stat.trainingspartner.inc.php', 'Trainingspartner', 'Wie oft hast du mit wem gemeinsam trainiert?', '', 2, 9),
(15, 'RunalyzePluginStat_Hoehenmeter', 'stat', 'stat.hoehenmeter.inc.php', 'H&ouml;henmeter', 'Die steilsten und bergigsten L&auml;ufe sowie der &Uuml;berblick &uuml;ber die absolvierten H&ouml;henmeter aller Monate.', '', 2, 10),
(16, 'RunalyzePluginStat_Laufabc', 'stat', 'stat.laufabc.inc.php', 'Lauf-ABC', 'Wie oft hast du Lauf-ABC absolviert?', '', 1, 11),
(17, 'RunalyzePluginTool_Cacheclean', 'tool', 'class.RunalyzePlugin_CachecleanTool.php', 'Cacheclean', 'L&ouml;scht den Cache der Diagramme. Sollte genutzt werden, falls Probleme mit Diagrammen auftauchen.', '', 1, 99),
(18, 'RunalyzePluginTool_DatenbankCleanup', 'tool', 'class.RunalyzePlugin_DatenbankCleanupTool.php', 'Datenbank-Cleanup', 'Reinigt die Datenbank. Dies ist unter Umst&auml;nden nach dem L&ouml;schen von Trainings notwendig.', '', 1, 99),
(19, 'RunalyzePluginTool_MultiEditor', 'tool', 'class.RunalyzePluginTool_MultiEditor.php', 'Multi-Editor', 'Bearbeitung von mehreren Trainings gleichzeitig.', 'sportid|bool=true|Sportart bearbeiten\ns|bool=true|Dauer bearbeiten\ndistance|bool=true|Distanz bearbeiten\nis_track|bool=false|Bahn bearbeiten\npulse|bool=true|Puls &oslash;/max bearbeiten\nkcal|bool=true|Kalorien bearbeiten\nabc|bool=false|Lauf-ABC bearbeiten\ncomment|bool=true|Bemerkung bearbeiten\nroute|bool=true|Strecke bearbeiten\nelevation|bool=false|hm bearbeiten\npartner|bool=false|Trainingspartner bearbeiten\ntemperature|bool=false|Temperatur bearbeiten\nweather|bool=false|Wetter bearbeiten\nclothes|bool=false|Kleidung bearbeiten\nsplits|bool=false|Zwischenzeiten bearbeiten\n', 1, 99),
(20, 'RunalyzePluginTool_AnalyzeVDOT', 'tool', 'class.RunalyzePluginTool_AnalyzeVDOT.php', 'VDOT analysieren', 'Den VDOT im Zusammenhang mit Wettkampfergebnissen analysieren', '', 1, 99);

--
-- Daten f&uuml;r Tabelle `runalyze_shoe`
--


--
-- Daten f&uuml;r Tabelle `runalyze_sport`
--

INSERT INTO `runalyze_sport` (`id`, `name`, `img`, `online`, `short`, `kcal`, `HFavg`, `RPE`, `distances`, `kmh`, `types`, `pulse`, `outside`) VALUES
(1, 'Laufen', 'laufen.gif', 1, 0, 880, 140, 4, 1, 0, 1, 1, 1),
(2, 'Radfahren', 'radfahren.gif', 1, 0, 770, 120, 2, 1, 1, 0, 1, 1),
(3, 'Schwimmen', 'schwimmen.gif', 1, 0, 743, 130, 5, 1, 1, 0, 0, 0),
(4, 'Gymnastik', 'gymnastik.gif', 1, 1, 280, 100, 1, 0, 0, 0, 0, 0),
(5, 'Sonstiges', 'unknown.gif', 1, 0, 500, 120, 3, 0, 0, 0, 0, 0);

--
-- Daten f&uuml;r Tabelle `runalyze_type`
--

INSERT INTO `runalyze_type` (`id`, `name`, `abbr`, `RPE`, `splits`) VALUES
(1, 'Dauerlauf', 'DL', 4, 0),
(2, 'Fahrtspiel', 'FS', 5, 0),
(3, 'Intervalltraining', 'IT', 7, 1),
(4, 'Tempodauerlauf', 'TDL', 7, 1),
(5, 'Wettkampf', 'WK', 10, 1),
(6, 'Regenerationslauf', 'RL', 2, 0),
(7, 'Langer Lauf', 'LL', 5, 0),
(8, 'Warm-/Auslaufen', 'WA', 1, 0);