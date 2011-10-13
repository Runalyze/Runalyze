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
-- Daten für Tabelle `runalyze_clothes`
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
-- Daten für Tabelle `runalyze_conf`
--

INSERT INTO `runalyze_conf` (`id`, `category`, `key`, `type`, `value`, `description`, `select_description`) VALUES
(1, 'Allgemein', 'GENDER', 'select', 'm=true|f=false', 'Geschlecht', 'm&auml;nnlich, weiblich'),
(2, 'Allgemein', 'PULS_MODE', 'select', 'bpm=false|hfmax=true', 'Pulsanzeige', 'absoluter Wert, &#37; HFmax'),
(3, 'Allgemein', 'USE_PULS', 'bool', 'true', 'Pulsdaten speichern', ''),
(4, 'Allgemein', 'USE_WETTER', 'bool', 'true', 'Wetter speichern', ''),
(5, 'Allgemein', 'PLZ', 'int', '0', 'f&uuml;r Wetter-Daten: PLZ', ''),
(6, 'Rechenspiele', 'RECHENSPIELE', 'bool', 'true', 'Rechenspiele aktivieren', ''),
(7, 'Training', 'MAINSPORT', 'selectdb', '1', 'Haupt-Sportart', 'sport, name'),
(8, 'Training', 'RUNNINGSPORT', 'selectdb', '1', 'Lauf-Sportart', 'sport, name'),
(9, 'Training', 'WK_TYPID', 'selectdb', '5', 'Trainingstyp: Wettkampf', 'type, name'),
(10, 'Training', 'LL_TYPID', 'selectdb', '7', 'Trainingstyp: Langer Lauf', 'type, name'),
(11, 'Eingabeformular', 'COMPUTE_KCAL', 'bool', 'true', 'Kalorienverbrauch automatisch berechnen', ''),
(12, 'Eingabeformular', 'TRAINING_CREATE_MODE', 'select', 'tcx=false|garmin=true|form=false', 'Standard-Eingabemodus', 'tcx-Datei hochladen, GarminCommunicator, Standard-Formular'),
(13, 'Eingabeformular', 'TRAINING_ELEVATION_SERVER', 'select', 'google=true|geonames=false', 'Server f&uuml;r H&ouml;henkorrektur', 'maps.googleapis.com, ws.geonames.org'),
(14, 'Eingabeformular', 'TRAINING_DO_ELEVATION', 'bool', 'true', 'H&ouml;henkorrektur verwenden', ''),
(15, 'Training', 'TRAINING_MAP_COLOR', 'string', '#FF5500', 'Linienfarbe auf GoogleMaps-Karte (#RGB)', ''),
(16, 'Training', 'TRAINING_MAP_MARKER', 'bool', 'true', 'Kilometer-Markierungen anzeigen', ''),
(17, 'Training', 'TRAINING_MAPTYPE', 'select', 'G_NORMAL_MAP=false|G_HYBRID_MAP=true|G_SATELLITE_MAP=false|G_PHYSICAL_MAP=false', 'Typ der GoogleMaps-Karte', 'Normal, Hybrid, Sattelit, Physikalisch'),
(18, 'Rechenspiele', 'JD_USE_VDOT_CORRECTOR', 'bool', 'true', 'Individuelle VDOT-Korrektur verwenden', ''),
(19, 'hidden', 'MAX_ATL', 'int', '0', 'Maximal value for ATL', ''),
(20, 'hidden', 'MAX_CTL', 'int', '0', 'Maximal value for CTL', ''),
(21, 'hidden', 'MAX_TRIMP', 'int', '0', 'Maximal value for TRIMP', ''),
(22, 'Suchfenster', 'RESULTS_AT_PAGE', 'int', '15', 'Ergebnisse pro Seite', '');

--
-- Daten für Tabelle `runalyze_dataset`
--

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

--
-- Daten für Tabelle `runalyze_plugin`
--

INSERT INTO `runalyze_plugin` (`id`, `key`, `type`, `filename`, `name`, `description`, `config`, `active`, `order`) VALUES
(1, 'RunalyzePluginPanel_Sports', 'panel', 'panel.sports.inc.php', 'Sportarten', '&Uuml;bersicht der Leistungen aller Sportarten für den aktuellen Monat, das Jahr oder seit Anfang der Aufzeichnung.', '', 1, 1),
(2, 'RunalyzePluginPanel_Rechenspiele', 'panel', 'panel.rechenspiele.inc.php', 'Rechenspiele', 'Anzeige der Rechenspiele M&uuml;digkeit, Grundlagenausdauer und Trainingsform.', 'show_trainingpaces|bool=true|Empfohlene Trainingstempi anzeigen', 1, 2),
(3, 'RunalyzePluginPanel_Prognose', 'panel', 'panel.prognose.inc.php', 'Prognose', 'Anzeige der aktuellen Wettkampfprognose.', 'distances|array=1, 3, 5, 10, 21.1, 42.2|Distanzen f&uuml;r die Prognose (kommagetrennt)', 2, 3),
(4, 'RunalyzePluginPanel_Schuhe', 'panel', 'panel.schuhe.inc.php', 'Schuhe', 'Anzeige der bisher gelaufenen Kilometer mit den aktiven Schuhen, bei Bedarf auch der alten Schuhe.', '', 2, 4),
(5, 'RunalyzePluginPanel_Sportler', 'panel', 'panel.sportler.inc.php', 'Sportler', 'Anzeige der Sportlerdaten wie Gewicht und aktueller Ruhepuls (auch als Diagramm).', 'use_weight|bool=true|Gewicht protokollieren\r\nuse_body_fat|bool=true|Fettanteil protokollieren\r\nuse_pulse|bool=true|Ruhepuls protokollieren\r\nwunschgewicht|int=68.0|Wunschgewicht', 1, 5),
(17, 'RunalyzePluginTool_Cacheclean', 'tool', 'class.RunalyzePlugin_CachecleanTool.php', 'Cacheclean', 'L&ouml;scht den Cache der Diagramme. Sollte genutzt werden, falls Probleme mit Diagrammen auftauchen.', '', 1, 99),
(6, 'RunalyzePluginStat_Schuhe', 'stat', 'stat.schuhe.inc.php', 'Schuhe', 'Ausf&uuml;hrliche Statistiken zu den Schuhen: Durchschnittliche, maximale und absolute Leistung (Kilometer / Tempo).', '', 1, 4),
(7, 'RunalyzePluginStat_Analyse', 'stat', 'stat.analyse.inc.php', 'Analyse', 'Analyse des Trainings zum Tempo, der Distanz und den verschiedenen Trainingstypen.', 'use_type|bool=true|Trainingstypen analysieren\r\nuse_pace|bool=true|Tempobereiche analysieren\r\nuse_pulse|bool=true|Pulsbereiche analysieren\r\nlowest_pulsegroup|int=65|Niedrigster Pulsbereich (%HFmax)\r\npulsegroup_step|int=5|Pulsbereich: Schrittweite\r\nlowest_pacegroup|int=360|Niedrigster Tempobereich (s/km)\r\nhighest_pacegroup|int=210|H&ouml;chster Tempobereich (s/km)\r\npacegroup_step|int=15|Tempobereich: Schrittweite', 1, 2),
(8, 'RunalyzePluginStat_Statistiken', 'stat', 'stat.statistiken.inc.php', 'Statistiken', 'Allgemeine Statistiken: Monatszusammenfassung in der Jahres&uuml;bersicht für alle Sportarten.', '', 1, 1),
(9, 'RunalyzePluginStat_Wettkampf', 'stat', 'stat.wettkampf.inc.php', 'Wettk&auml;mpfe', 'Bestzeiten und alles weitere zu den bisher gelaufenen Wettk&auml;mpfen.', 'last_wk_num|int=10|Anzahl f&uuml;r letzte Wettk&auml;mpfe main_distance|int=10|Hauptdistanz (wird als Diagramm dargestellt) pb_distances|array=1, 3, 5, 10, 21.1, 42.2|Distanzen f&uuml;r Bestzeit-Vergleich (kommagetrennt) fun_ids|array=|IDs der Spa&szlig;-Wettk&auml;mpfe (nicht per Hand editieren!)', 1, 3),
(10, 'RunalyzePluginStat_Wetter', 'stat', 'stat.wetter.inc.php', 'Wetter', 'Wetterverh&auml;ltnisse, Temperaturen und die getragenen Kleidungsst&uuml;cke.', 'for_weather|bool=true|Wetter-Statistiken anzeigen\r\nfor_clothes|bool=true|Kleidung-Statistiken anezigen', 1, 5),
(11, 'RunalyzePluginStat_Rekorde', 'stat', 'stat.rekorde.inc.php', 'Rekorde', 'Am schnellsten, am l&auml;ngsten, am weitesten: Die Rekorde aus dem Training.', '', 2, 6),
(12, 'RunalyzePluginStat_Strecken', 'stat', 'stat.strecken.inc.php', 'Strecken', 'Auflistung der h&auml;ufigsten und seltensten Strecken/Orte.', '', 2, 7),
(13, 'RunalyzePluginStat_Trainingszeiten', 'stat', 'stat.trainingszeiten.inc.php', 'Trainingszeiten', 'Auflistung n&auml;chtlicher Trainings und Diagramme &uuml;ber die Trainingszeiten.', '', 2, 8),
(14, 'RunalyzePluginStat_Trainingspartner', 'stat', 'stat.trainingspartner.inc.php', 'Trainingspartner', 'Wie oft hast du mit wem gemeinsam trainiert?', '', 2, 9),
(15, 'RunalyzePluginStat_Hoehenmeter', 'stat', 'stat.hoehenmeter.inc.php', 'H&ouml;henmeter', 'Die steilsten und bergigsten L&auml;ufe sowie der &Uuml;berblick &uuml;ber die absolvierten H&ouml;henmeter aller Monate.', '', 2, 10),
(16, 'RunalyzePluginStat_Laufabc', 'stat', 'stat.laufabc.inc.php', 'Lauf-ABC', 'Wie oft hast du Lauf-ABC absolviert?', '', 2, 11),
(18, 'RunalyzePluginTool_DatenbankCleanup', 'tool', 'class.RunalyzePlugin_DatenbankCleanupTool.php', 'Datenbank-Cleanup', 'Reinigt die Datenbank. Dies ist unter Umst&auml;nden nach dem L&ouml;schen von Trainings notwendig.', '', 1, 99);

--
-- Daten für Tabelle `runalyze_shoe`
--


--
-- Daten für Tabelle `runalyze_sport`
--

INSERT INTO `runalyze_sport` (`id`, `name`, `img`, `online`, `short`, `kcal`, `HFavg`, `RPE`, `distances`, `kmh`, `types`, `pulse`, `outside`) VALUES
(1, 'Laufen', 'laufen.gif', 1, 0, 880, 140, 4, 1, 0, 1, 1, 1),
(2, 'Radfahren', 'radfahren.gif', 1, 0, 770, 120, 2, 1, 1, 0, 1, 1),
(3, 'Schwimmen', 'schwimmen.gif', 1, 0, 743, 130, 5, 1, 1, 0, 0, 0),
(4, 'Gymnastik', 'gymnastik.gif', 1, 1, 280, 100, 1, 0, 0, 0, 0, 0),
(5, 'Sonstiges', 'unknown.gif', 1, 0, 500, 120, 3, 0, 0, 0, 0, 0);

--
-- Daten für Tabelle `runalyze_training`
--


--
-- Daten für Tabelle `runalyze_type`
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

--
-- Daten für Tabelle `runalyze_user`
--


--
-- Daten für Tabelle `runalyze_weather`
--

INSERT INTO `runalyze_weather` (`id`, `name`, `img`, `order`) VALUES
(1, 'unbekannt', 'ka.gif', 0),
(2, 'sonnig', 'sonnig.gif', 1),
(3, 'heiter', 'heiter.gif', 2),
(4, 'bew&ouml;lkt', 'bewoelkt.gif', 3),
(5, 'wechselhaft', 'wechselhaft.gif', 4),
(6, 'regnerisch', 'regnerisch.gif', 5),
(7, 'Schnee', 'Schnee.gif', 6);
