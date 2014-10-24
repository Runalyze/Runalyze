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
(9, 'Mütze', 'Mütze', 4);

--
-- Daten f&uuml;r Tabelle `runalyze_dataset`
--

INSERT INTO `runalyze_dataset` (`id`, `name`, `modus`, `class`, `style`, `position`, `summary`, `summary_mode`) VALUES
(1, 'sportid', 3, '', '', 4, 0, 'YES'),
(2, 'typeid', 2, '', '', 3, 0, 'NO'),
(3, 'time', 1, 'c', '', 0, 0, 'NO'),
(4, 'distance', 2, '', '', 5, 1, 'SUM'),
(5, 's', 3, '', '', 6, 1, 'SUM'),
(6, 'pace', 2, 'small', '', 7, 1, 'AVG'),
(7, 'elevation', 2, 'small', '', 9, 1, 'SUM'),
(8, 'kcal', 2, 'small', '', 10, 1, 'SUM'),
(9, 'pulse_avg', 2, 'small', 'font-style:italic;', 8, 1, 'AVG'),
(10, 'pulse_max', 1, 'small', '', 0, 0, 'MAX'),
(11, 'trimp', 2, '', '', 13, 1, 'SUM'),
(12, 'temperature', 2, 'small', 'width:35px;', 2, 0, 'AVG'),
(13, 'weatherid', 2, '', '', 1, 0, 'NO'),
(14, 'route', 1, 'small l', '', 18, 0, 'NO'),
(15, 'clothes', 1, 'small l', '', 16, 0, 'NO'),
(16, 'splits', 2, '', '', 11, 0, 'NO'),
(17, 'comment', 2, 'small l', '', 12, 0, 'NO'),
(18, 'shoeid', 1, 'small l', '', 0, 0, 'NO'),
(19, 'vdot', 2, '', '', 14, 1, 'AVG'),
(20, 'partner', 1, 'small', '', 17, 0, 'NO'),
(21, 'abc', 1, '', '', 15, 0, 'NO');
(22, 'cadence', 1, 'small', '', 19, 1, 'AVG'),
(23, 'power', 1, 'small', '', 20, 1, 'SUM'),
(24, 'jd_intensity', 2, '', '', 22, 1, 'SUM');

--
-- Daten f&uuml;r Tabelle `runalyze_plugin`
--

INSERT INTO `runalyze_plugin` (`id`, `key`, `type`, `active`, `order`) VALUES
(1, 'RunalyzePluginPanel_Sports', 'panel', 1, 1),
(2, 'RunalyzePluginPanel_Rechenspiele', 'panel', 1, 2),
(3, 'RunalyzePluginPanel_Prognose', 'panel', 2, 3),
(4, 'RunalyzePluginPanel_Schuhe', 'panel', 2, 4),
(5, 'RunalyzePluginPanel_Sportler', 'panel', 1, 5),
(6, 'RunalyzePluginStat_Analyse', 'stat', 1, 2),
(7, 'RunalyzePluginStat_Statistiken', 'stat', 1, 1),
(8, 'RunalyzePluginStat_Wettkampf', 'stat', 1, 3),
(9, 'RunalyzePluginStat_Wetter', 'stat', 1, 5),
(10, 'RunalyzePluginStat_Rekorde', 'stat', 2, 6),
(11, 'RunalyzePluginStat_Strecken', 'stat', 2, 7),
(12, 'RunalyzePluginStat_Trainingszeiten', 'stat', 2, 8),
(13, 'RunalyzePluginStat_Trainingspartner', 'stat', 2, 9),
(14, 'RunalyzePluginStat_Hoehenmeter', 'stat', 2, 10),
(15, 'RunalyzePluginStat_Laufabc', 'stat', 1, 11),
(16, 'RunalyzePluginTool_Cacheclean', 'tool', 1, 99),
(17, 'RunalyzePluginTool_DatenbankCleanup', 'tool', 1, 99),
(18, 'RunalyzePluginTool_MultiEditor', 'tool', 1, 99),
(19, 'RunalyzePluginTool_AnalyzeVDOT', 'tool', 1, 99),
(20, 'RunalyzePluginTool_DbBackup', 'tool', 1, 99),
(21, 'RunalyzePluginTool_JDTables', 'tool', 1, 99);

--
-- Daten f&uuml;r Tabelle `runalyze_shoe`
--


--
-- Daten f&uuml;r Tabelle `runalyze_sport`
--

INSERT INTO `runalyze_sport` (`id`, `name`, `img`, `short`, `kcal`, `HFavg`, `RPE`, `distances`, `speed`, `types`, `pulse`, `power`, `outside`) VALUES
(1, 'Laufen', 'laufen.gif', 0, 880, 140, 4, 1, "min/km", 1, 1, 0, 1),
(2, 'Radfahren', 'radfahren.gif', 0, 770, 120, 2, 1, "km/h", 0, 1, 1, 1),
(3, 'Schwimmen', 'schwimmen.gif', 0, 743, 130, 5, 1, "min/100m", 0, 0, 0, 0),
(4, 'Gymnastik', 'gymnastik.gif', 1, 280, 100, 1, 0, "", 0, 0, 0, 0),
(5, 'Sonstiges', 'unknown.gif', 0, 500, 120, 3, 0, "", 0, 0, 0, 0);

--
-- Daten f&uuml;r Tabelle `runalyze_type`
--

INSERT INTO `runalyze_type` (`id`, `name`, `abbr`, `RPE`, `sportid`) VALUES
(1, 'Dauerlauf', 'DL', 4, 1),
(2, 'Fahrtspiel', 'FS', 5, 1),
(3, 'Intervalltraining', 'IT', 7, 1),
(4, 'Tempodauerlauf', 'TDL', 7, 1),
(5, 'Wettkampf', 'WK', 10, 1),
(6, 'Regenerationslauf', 'RL', 2, 1),
(7, 'Langer Lauf', 'LL', 5, 1),
(8, 'Warm-/Auslaufen', 'WA', 1, 1);