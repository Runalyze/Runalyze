-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 14. Juli 2011 um 19:40
-- Server Version: 5.1.41
-- PHP-Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Daten für Tabelle `runalyze_config`
--

INSERT INTO `runalyze_config` (`id`, `geschlecht`, `wunschgewicht`, `use_schuhe`, `use_kleidung`, `use_temperatur`, `use_wetter`, `use_strecke`, `use_splits`, `use_puls`, `use_koerperfett`, `puls_mode`, `use_gewicht`, `use_ruhepuls`, `show_user`, `show_prognose`, `show_rechenspiele`, `max_belastung`, `max_atl`, `max_ctl`, `max_trimp`) VALUES
(1, 'm', '0.0', 1, 1, 1, 1, 1, 1, 1, 1, 'hfmax', 1, 1, 1, 1, 1, 0, 0, 0, 0);

--
-- Daten für Tabelle `runalyze_dataset`
--

INSERT INTO `runalyze_dataset` (`id`, `name`, `function`, `beschreibung`, `distanz`, `outside`, `puls`, `typ`, `modus`, `class`, `style`, `position`, `zusammenfassung`, `zf_mode`) VALUES
(1, 'sportid', 'dataset_sport();', 'Anzeige des Symbols der jeweiligen Sportart, welches mit dem Informationsfenster mit allen Daten verknüpft ist.', 0, 0, 0, 0, 3, '', '', 4, 0, 'YES'),
(2, 'typid', 'dataset_typ();', 'Anzeige der Abkürzung für den Trainingstyp wie Intervalltraining (IT) oder Dauerlauf (DL).', 0, 0, 0, 1, 2, '', '', 3, 0, 'NO'),
(3, 'time', 'dataset_time();', 'Anzeige der Uhrzeit. Datum und Wochentag werden automatisch angezeigt.', 0, 0, 0, 0, 1, 'c', '', 0, 0, 'NO'),
(4, 'distanz', 'dataset_distanz();', 'Anzeige der Distanz in Kilometern, bei Bahn-Angaben in Metern.', 1, 0, 0, 0, 2, '', '', 5, 1, 'SUM'),
(5, 'dauer', 'dataset_dauer();', 'Anzeige der Trainingsdauer.', 0, 0, 0, 0, 3, '', '', 6, 1, 'SUM'),
(6, 'pace', 'dataset_pace();', 'Anzeige des Tempos, je nach Sportart in km/h oder min/km.', 1, 0, 0, 0, 2, 'small', '', 7, 1, 'AVG'),
(7, 'hm', 'dataset_hm();', 'Anzeige der bewältigten Höhenmeter.', 1, 1, 0, 0, 2, 'small', '', 9, 1, 'SUM'),
(8, 'kalorien', 'dataset_kalorien();', 'Anzeige der (vermutlich) verbrauchten Kalorien.', 0, 0, 0, 0, 2, 'small', '', 10, 1, 'SUM'),
(9, 'puls', 'dataset_puls();', 'Anzeige des durchschnittlichen Pulses je nach Einstellung als absoluter Wert oder als Prozent der maximalen Herzfrequenz.', 0, 0, 1, 0, 2, 'small', 'font-style:italic;', 8, 1, 'AVG'),
(10, 'puls_max', 'dataset_puls_max();', 'Anzeige des maximalen Pulses beim Training.', 0, 0, 1, 0, 1, 'small', '', 0, 0, 'MAX'),
(11, 'trimp', 'dataset_trimp();', 'Anzeige des Belastungswertes "TRainingsIMPulse".', 0, 0, 0, 0, 2, '', '', 13, 1, 'SUM'),
(12, 'temperatur', 'dataset_temperatur();', 'Anzeige der Temperatur', 0, 1, 0, 0, 2, 'small', 'width:35px;', 2, 0, 'AVG'),
(13, 'wetterid', 'dataset_wetter();', 'Anzeige des Wettersymbols', 0, 1, 0, 0, 2, '', '', 1, 0, 'NO'),
(14, 'strecke', 'dataset_strecke();', 'Anzeige des Streckenverlaufs', 1, 1, 0, 0, 1, 'small l', '', 18, 0, 'NO'),
(15, 'kleidung', 'dataset_kleidung();', 'Anzeige der benutzten Kleidung.', 0, 1, 0, 0, 1, 'small l', '', 16, 0, 'NO'),
(16, 'splits', 'dataset_splits();', 'Anzeige der Splits beim Intervalltraining oder Wettkampf.', 1, 0, 0, 1, 2, '', '', 11, 0, 'NO'),
(17, 'bemerkung', 'dataset_bemerkung();', 'Anzeige der Bemerkung (auf 25 Zeichen gekürzt) sowie ein Link zu dem Split-Diagramm, falls Splitzeiten vorhanden sind.', 0, 0, 0, 0, 2, 'small l', '', 12, 0, 'NO'),
(18, 'schuhid', 'dataset_schuh();', 'Anzeige des benutzten Schuhs.', 1, 1, 0, 0, 1, 'small l', '', 0, 0, 'NO'),
(19, 'vdot', 'dataset_vdot();', 'Anzeige der aus dem Lauf (mittels der Pulsdaten) berechneten Form.', 1, 0, 1, 1, 2, '', '', 14, 1, 'AVG'),
(20, 'trainingspartner', 'dataset_trainingspartner();', 'Anzeige der Trainingspartner, mit denen man trainiert hat.', 0, 0, 0, 0, 1, 'small', '', 17, 0, 'NO'),
(21, 'laufabc', 'dataset_laufabc();', 'Anzeige eines kleinen Symbols, wenn man beim Training das Lauf-ABC absolviert hat.', 0, 0, 0, 1, 1, '', '', 15, 0, 'NO');

--
-- Daten für Tabelle `runalyze_kleidung`
--

INSERT INTO `runalyze_kleidung` (`id`, `name`, `name_kurz`, `order`) VALUES
(1, 'Langarmshirt', 'S-Lang', 1),
(2, 'T-Shirt', 'Shirt', 1),
(3, 'Singlet', 'Singlet', 1),
(4, 'Jacke', 'Jacke', 1),
(5, 'Muetze', 'Muetze', 5),
(6, 'Handschuhe', 'Handschuhe', 3),
(7, 'kurze Hose', 'H-kurz', 2),
(8, 'lange Hose', 'H-lang', 2),
(9, 'Laufshorts', 'Shorts', 2);

--
-- Daten für Tabelle `runalyze_plugin`
--

INSERT INTO `runalyze_plugin` (`id`, `key`, `type`, `filename`, `name`, `description`, `config`, `active`, `order`) VALUES
(1, 'RunalyzePlugin_SportsPanel', 'panel', 'panel.sports.inc.php', 'Sportarten', '&Uuml;bersicht der Leistungen aller Sportarten für den aktuellen Monat, das Jahr oder seit Anfang der Aufzeichnung.', '', 1, 1),
(2, 'RunalyzePlugin_RechenspielePanel', 'panel', 'panel.rechenspiele.inc.php', 'Rechenspiele', 'Anzeige der Rechenspiele M&uuml;digkeit, Grundlagenausdauer und Trainingsform.', '', 1, 2),
(3, 'RunalyzePlugin_PrognosePanel', 'panel', 'panel.prognose.inc.php', 'Prognose', 'Anzeige der aktuellen Wettkampfprognose.', 'distances|array=1, 3, 5, 10, 21.1, 42.2|Distanzen f&uuml;r die Prognose (kommagetrennt)', 2, 3),
(4, 'RunalyzePlugin_SchuhePanel', 'panel', 'panel.schuhe.inc.php', 'Schuhe', 'Anzeige der bisher gelaufenen Kilometer mit den aktiven Schuhen, bei Bedarf auch der alten Schuhe.', '', 2, 4),
(5, 'RunalyzePlugin_SportlerPanel', 'panel', 'panel.sportler.inc.php', 'Sportler', 'Anzeige der Sportlerdaten wie Gewicht und aktueller Ruhepuls (auch als Diagramm).', 'use_weight|bool=true|Gewicht protokollieren\r\nuse_body_fat|bool=true|Fettanteil protokollieren\r\nuse_pulse|bool=true|Ruhepuls protokollieren\r\nuse_blood_pressure|bool=false|Blutdruck protokollieren\r\nwunschgewicht|int=68.0|Wunschgewicht', 1, 5),
(6, 'RunalyzePlugin_SchuheStat', 'stat', 'stat.schuhe.inc.php', 'Schuhe', 'Ausf&uuml;hrliche Statistiken zu den Schuhen: Durchschnittliche, maximale und absolute Leistung (Kilometer / Tempo).', '', 1, 4),
(7, 'RunalyzePlugin_AnalyseStat', 'stat', 'stat.analyse.inc.php', 'Analyse', 'Analyse des Trainings zum Tempo, der Distanz und den verschiedenen Trainingstypen.', 'use_type|bool=true|Trainingstypen analysieren\r\nuse_pace|bool=true|Tempobereiche analysieren\r\nuse_pulse|bool=true|Pulsbereiche analysieren\r\nlowest_pulsegroup|int=65|Niedrigster Pulsbereich (%HFmax)\r\npulsegroup_step|int=5|Pulsbereich: Schrittweite\r\nlowest_pacegroup|int=360|Niedrigster Tempobereich (s/km)\r\nhighest_pacegroup|int=210|H&ouml;chster Tempobereich (s/km)\r\npacegroup_step|int=15|Tempobereich: Schrittweite', 1, 2),
(8, 'RunalyzePlugin_StatistikenStat', 'stat', 'stat.statistiken.inc.php', 'Statistiken', 'Allgemeine Statistiken: Monatszusammenfassung in der Jahres&uuml;bersicht für alle Sportarten.', '', 1, 1),
(9, 'RunalyzePlugin_WettkampfStat', 'stat', 'stat.wettkampf.inc.php', 'Wettk&auml;mpfe', 'Bestzeiten und alles weitere zu den bisher gelaufenen Wettk&auml;mpfen.', 'last_wk_num|int=10|Anzahl f&uuml;r letzte Wettk&auml;mpfe\r\nmain_distance|int=10|Hauptdistanz (wird als Diagramm dargestellt)\r\npb_distances|array=1, 3, 5, 10, 21.1, 42.2|Distanzen f&uuml;r Bestzeit-Vergleich (kommagetrennt)', 1, 3),
(10, 'RunalyzePlugin_WetterStat', 'stat', 'stat.wetter.inc.php', 'Wetter', 'Wetterverh&auml;ltnisse, Temperaturen und die getragenen Kleidungsst&uuml;cke.', 'for_weather|bool=true|Wetter-Statistiken anzeigen\r\nfor_clothes|bool=true|Kleidung-Statistiken anezigen', 1, 5),
(11, 'RunalyzePlugin_RekordeStat', 'stat', 'stat.rekorde.inc.php', 'Rekorde', 'Am schnellsten, am l&auml;ngsten, am weitesten: Die Rekorde aus dem Training.', '', 2, 6),
(12, 'RunalyzePlugin_StreckenStat', 'stat', 'stat.strecken.inc.php', 'Strecken', 'Auflistung der h&auml;ufigsten und seltensten Strecken/Orte.', '', 2, 7),
(13, 'RunalyzePlugin_TrainingszeitenStat', 'stat', 'stat.trainingszeiten.inc.php', 'Trainingszeiten', 'Auflistung n&auml;chtlicher Trainings und Diagramme &uuml;ber die Trainingszeiten.', '', 2, 8),
(14, 'RunalyzePlugin_TrainingspartnerStat', 'stat', 'stat.trainingspartner.inc.php', 'Trainingspartner', 'Wie oft hast du mit wem gemeinsam trainiert?', '', 2, 9),
(15, 'RunalyzePlugin_HoehenmeterStat', 'stat', 'stat.hoehenmeter.inc.php', 'H&ouml;henmeter', 'Die steilsten und bergigsten L&auml;ufe sowie der &Uuml;berblick &uuml;ber die absolvierten H&ouml;henmeter aller Monate.', '', 2, 10),
(16, 'RunalyzePlugin_LaufabcStat', 'stat', 'stat.laufabc.inc.php', 'Lauf-ABC', 'Wie oft hast du Lauf-ABC absolviert?', '', 2, 11);

--
-- Daten für Tabelle `runalyze_schuhe`
--


--
-- Daten für Tabelle `runalyze_sports`
--

INSERT INTO `runalyze_sports` (`id`, `name`, `bild`, `online`, `short`, `kalorien`, `HFavg`, `RPE`, `distanztyp`, `kmh`, `typen`, `pulstyp`, `outside`, `distanz`, `dauer`) VALUES
(1, 'Laufen', 'laufen.gif', 1, 0, 880, 140, 4, 1, 0, 1, 1, 1, '0.00', 0),
(2, 'Radfahren', 'radfahren.gif', 1, 0, 770, 120, 2, 1, 1, 0, 1, 1, '0.00', 0),
(3, 'Schwimmen', 'schwimmen.gif', 1, 0, 743, 130, 5, 1, 1, 0, 0, 0, '0.00', 0),
(4, 'Gymnastik', 'gymnastik.gif', 1, 1, 280, 100, 1, 0, 0, 0, 0, 0, '0.00', 0),
(5, 'Sonstiges', 'unknown.gif', 1, 0, 500, 120, 3, 0, 0, 0, 0, 0, '0.00', 0);

--
-- Daten für Tabelle `runalyze_training`
--


--
-- Daten für Tabelle `runalyze_typ`
--

INSERT INTO `runalyze_typ` (`id`, `name`, `abk`, `RPE`, `splits`, `count`) VALUES
(1, 'Dauerlauf', 'DL', 4, 0, 1),
(2, 'Fahrtspiel', 'FS', 5, 0, 1),
(3, 'Intervalltraining', 'IT', 7, 1, 1),
(4, 'Tempodauerlauf', 'TDL', 7, 1, 1),
(5, 'Wettkampf', 'WK', 10, 1, 1),
(6, 'Regenerationslauf', 'RL', 2, 0, 1),
(7, 'Langer Lauf', 'LL', 5, 0, 1),
(8, 'Warm-/Auslaufen', 'WA', 1, 0, 0);

--
-- Daten für Tabelle `runalyze_user`
--


--
-- Daten für Tabelle `runalyze_wetter`
--

INSERT INTO `runalyze_wetter` (`id`, `name`, `bild`, `order`) VALUES
(1, 'unbekannt', 'ka.gif', 0),
(2, 'sonnig', 'sonnig.gif', 1),
(3, 'heiter', 'heiter.gif', 2),
(4, 'bew&ouml;lkt', 'bewoelkt.gif', 3),
(5, 'wechselhaft', 'wechselhaft.gif', 4),
(6, 'regnerisch', 'regnerisch.gif', 5),
(7, 'Schnee', 'Schnee.gif', 6);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
