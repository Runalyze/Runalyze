<?php
/**
 * This file contains all empty values needed to create a new account.
 * 
 * Structure:
 * $EmptyTables['TABLENAME_WITHOUT_PREFIX'] = array('columns' => array(...), 'values' => array( array(...), ... )); 
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
$EmptyTables = array();
$EmptyTables['clothes'] = array(
	'columns' => array('name', 'short', 'order'),
	'values'  => array(
		array('Langarmshirt', 'S-Lang', 1),
		array('T-Shirt', 'Shirt', 1),
		array('Singlet', 'Singlet', 1),
		array('Jacke', 'Jacke', 1),
		array('kurze Hose', 'H-kurz', 2),
		array('lange Hose', 'H-lang', 2),
		array('Laufshorts', 'Shorts', 2),
		array('Handschuhe', 'Handschuhe', 3),
		array('Muetze', 'Muetze', 4)
	)
);
$EmptyTables['dataset'] = array(
	'columns' => array('name', 'label', 'description', 'distance', 'outside', 'pulse', 'type', 'modus', 'class', 'style', 'position', 'summary', 'summary_mode'),
	'values'  => array(
		array('sportid', 'Sportart', 'Anzeige des Symbols der jeweiligen Sportart, welches mit dem Informationsfenster mit allen Daten verkn&uuml;pft ist.', 0, 0, 0, 0, 3, '', '', 4, 0, 'YES'),
		array('typeid', 'Trainingstyp', 'Anzeige der Abk&uuml;rzung f&uuml;r den Trainingstyp wie Intervalltraining (IT) oder Dauerlauf (DL).', 0, 0, 0, 1, 2, '', '', 3, 0, 'NO'),
		array('time', 'Uhrzeit', 'Anzeige der Uhrzeit. Datum und Wochentag werden automatisch angezeigt.', 0, 0, 0, 0, 1, 'c', '', 0, 0, 'NO'),
		array('distance', 'Distanz', 'Anzeige der Distanz in Kilometern, bei Bahn-Angaben in Metern.', 1, 0, 0, 0, 2, '', '', 5, 1, 'SUM'),
		array('s', 'Dauer', 'Anzeige der Trainingsdauer.', 0, 0, 0, 0, 3, '', '', 6, 1, 'SUM'),
		array('pace', 'Pace', 'Anzeige des Tempos, je nach Sportart in km/h oder min/km.', 1, 0, 0, 0, 2, 'small', '', 7, 1, 'AVG'),
		array('elevation', 'H&ouml;henmeter', 'Anzeige der bew&auml;ltigten a&ouml;henmeter.', 1, 1, 0, 0, 2, 'small', '', 9, 1, 'SUM'),
		array('kcal', 'Kalorien', 'Anzeige der (vermutlich) verbrauchten Kalorien.', 0, 0, 0, 0, 2, 'small', '', 10, 1, 'SUM'),
		array('pulse_avg', 'durchschn. Puls', 'Anzeige des durchschnittlichen Pulses je nach Einstellung als absoluter Wert oder als Prozent der maximalen Herzfrequenz.', 0, 0, 1, 0, 2, 'small', 'font-style:italic;', 8, 1, 'AVG'),
		array('pulse_max', 'max. Puls', 'Anzeige des maximalen Pulses beim Training.', 0, 0, 1, 0, 1, 'small', '', 0, 0, 'MAX'),
		array('trimp', 'TRIMP', 'Anzeige des Belastungswertes "TRainingsIMPulse".', 0, 0, 0, 0, 2, '', '', 13, 1, 'SUM'),
		array('temperature', 'Temperatur', 'Anzeige der Temperatur', 0, 1, 0, 0, 2, 'small', 'width:35px;', 2, 0, 'AVG'),
		array('weatherid', 'Wetter', 'Anzeige des Wettersymbols', 0, 1, 0, 0, 2, '', '', 1, 0, 'NO'),
		array('route', 'Strecke', 'Anzeige des Streckenverlaufs', 1, 1, 0, 0, 1, 'small l', '', 18, 0, 'NO'),
		array('clothes', 'Kleidung', 'Anzeige der benutzten Kleidung.', 0, 1, 0, 0, 1, 'small l', '', 16, 0, 'NO'),
		array('splits', 'Zwischenzeiten', 'Anzeige der Splits beim Intervalltraining oder Wettkampf.', 1, 0, 0, 1, 2, '', '', 11, 0, 'NO'),
		array('comment', 'Bemerkung', 'Anzeige der Bemerkung (auf 25 Zeichen gek&uuml;rzt) sowie ein Link zu dem Split-Diagramm, falls Splitzeiten vorhanden sind.', 0, 0, 0, 0, 2, 'small l', '', 12, 0, 'NO'),
		array('shoeid', 'Schuh', 'Anzeige des benutzten Schuhs.', 1, 1, 0, 0, 1, 'small l', '', 0, 0, 'NO'),
		array('vdot', 'VDOT', 'Anzeige der aus dem Lauf (mittels der Pulsdaten) berechneten Form.', 1, 0, 1, 1, 2, '', '', 14, 1, 'AVG'),
		array('partner', 'Trainingspartner', 'Anzeige der Trainingspartner, mit denen man trainiert hat.', 0, 0, 0, 0, 1, 'small', '', 17, 0, 'NO'),
		array('abc', 'Lauf-ABC', 'Anzeige eines kleinen Syabols, wenn man beim Training das Lauf-ABC absolviert hat.', 0, 0, 0, 1, 1, '', '', 15, 0, 'NO')
	)
);
$EmptyTables['plugin'] = array(
	'columns' => array('key', 'type', 'filename', 'name', 'description', 'config', 'active', 'order'),
	'values'  => array(
		array('RunalyzePluginPanel_Sports', 'panel', 'panel.sports.inc.php', 'Sportarten', '&Uuml;bersicht der Leistungen aller Sportarten f&uuml;r den aktuellen Monat, das Jahr oder seit Anfang der Aufzeichnung.', '', 1, 1),
		array('RunalyzePluginPanel_Rechenspiele', 'panel', 'panel.rechenspiele.inc.php', 'Rechenspiele', 'Anzeige der Rechenspiele M&uuml;digkeit, Grundlagenausdauer und Trainingsform.', 'show_trainingpaces|bool=true|Empfohlene Trainingstempi anzeigen\n', 1, 2),
		array('RunalyzePluginPanel_Prognose', 'panel', 'panel.prognose.inc.php', 'Prognose', 'Anzeige der aktuellen Wettkampfprognose.', 'distances|array=1, 3, 5, 10, 21.1, 42.2|Distanzen f&uuml;r die Prognose (kommagetrennt)', 2, 3),
		array('RunalyzePluginPanel_Schuhe', 'panel', 'panel.schuhe.inc.php', 'Schuhe', 'Anzeige der bisher gelaufenen Kilometer mit den aktiven Schuhen, bei Bedarf auch der alten Schuhe.', '', 2, 4),
		array('RunalyzePluginPanel_Sportler', 'panel', 'panel.sportler.inc.php', 'Sportler', 'Anzeige der Sportlerdaten wie Gewicht und aktueller Ruhepuls (auch als Diagramm).', 'use_weight|bool=true|Gewicht protokollieren\nuse_body_fat|bool=true|Fettanteil protokollieren\nuse_pulse|bool=true|Ruhepuls protokollieren\nwunschgewicht|int=66.0|Wunschgewicht\n', 1, 5),
		array('RunalyzePluginStat_Analyse', 'stat', 'stat.analyse.inc.php', 'Analyse', 'Analyse des Trainings zum Tempo, der Distanz und den verschiedenen Trainingstypen.', 'use_type|bool=true|Trainingstypen analysieren\r\nuse_pace|bool=true|Tempobereiche analysieren\r\nuse_pulse|bool=true|Pulsbereiche analysieren\r\nlowest_pulsegroup|int=65|Niedrigster Pulsbereich (%HFmax)\r\npulsegroup_step|int=5|Pulsbereich: Schrittweite\r\nlowest_pacegroup|int=360|Niedrigster Tempobereich (s/km)\r\nhighest_pacegroup|int=210|H&ouml;chster Tempobereich (s/km)\r\npacegroup_step|int=15|Tempobereich: Schrittweite', 1, 2),
		array('RunalyzePluginStat_Statistiken', 'stat', 'stat.statistiken.inc.php', 'Statistiken', 'Allgemeine Statistiken: Monatszusammenfassung in der Jahres&uuml;bersicht f&uuml;r alle Sportarten.', '', 1, 1),
		array('RunalyzePluginStat_Wettkampf', 'stat', 'stat.wettkampf.inc.php', 'Wettk&auml;mpfe', 'Bestzeiten und alles weitere zu den bisher gelaufenen Wettk&auml;mpfen.', 'last_wk_num|int=10|Anzahl f&uuml;r letzte Wettk&auml;mpfe\nmain_distance|int=10|Hauptdistanz (wird als Diagramm dargestellt)\npb_distances|array=1,     3,     5,     10,     21.1,     42.2|Distanzen f&uuml;r Bestzeit-Vergleich (kommagetrennt)\nfun_ids|array=1453,     1248,  1078, 1252|IDs der Spa&szlig;-Wettk&auml;mpfe (nicht per Hand editieren!)\n', 1, 3),
		array('RunalyzePluginStat_Wetter', 'stat', 'stat.wetter.inc.php', 'Wetter', 'Wetterverh&auml;ltnisse, Temperaturen und die getragenen Kleidungsst&uuml;cke.', 'for_weather|bool=true|Wetter-Statistiken anzeigen\r\nfor_clothes|bool=true|Kleidung-Statistiken anezigen', 1, 5),
		array('RunalyzePluginStat_Rekorde', 'stat', 'stat.rekorde.inc.php', 'Rekorde', 'Am schnellsten, am l&auml;ngsten, am weitesten: Die Rekorde aus dem Training.', '', 2, 6),
		array('RunalyzePluginStat_Strecken', 'stat', 'stat.strecken.inc.php', 'Strecken', 'Auflistung der h&auml;ufigsten und seltensten Strecken/Orte.', '', 2, 7),
		array('RunalyzePluginStat_Trainingszeiten', 'stat', 'stat.trainingszeiten.inc.php', 'Trainingszeiten', 'Auflistung n&auml;chtlicher Trainings und Diagramme &uuml;ber die Trainingszeiten.', '', 2, 8),
		array('RunalyzePluginStat_Trainingspartner', 'stat', 'stat.trainingspartner.inc.php', 'Trainingspartner', 'Wie oft hast du mit wem gemeinsam trainiert?', '', 2, 9),
		array('RunalyzePluginStat_Hoehenmeter', 'stat', 'stat.hoehenmeter.inc.php', 'H&ouml;henmeter', 'Die steilsten und bergigsten L&auml;ufe sowie der &Uuml;berblick &uuml;ber die absolvierten H&ouml;henmeter aller Monate.', '', 2, 10),
		array('RunalyzePluginStat_Laufabc', 'stat', 'stat.laufabc.inc.php', 'Lauf-ABC', 'Wie oft hast du Lauf-ABC absolviert?', '', 1, 11),
		array('RunalyzePluginTool_Cacheclean', 'tool', 'class.RunalyzePlugin_CachecleanTool.php', 'Cacheclean', 'L&ouml;scht den Cache der Diagramme. Sollte genutzt werden, falls Probleme mit Diagrammen auftauchen.', '', 1, 99),
		array('RunalyzePluginTool_DatenbankCleanup', 'tool', 'class.RunalyzePlugin_DatenbankCleanupTool.php', 'Datenbank-Cleanup', 'Reinigt die Datenbank. Dies ist unter Umst&auml;nden nach dem L&ouml;schen von Trainings notwendig.', '', 1, 99),
		array('RunalyzePluginTool_MultiEditor', 'tool', 'class.RunalyzePluginTool_MultiEditor.php', 'Multi-Editor', 'Bearbeitung von mehreren Trainings gleichzeitig.', 'sportid|bool=true|Sportart bearbeiten\ns|bool=true|Dauer bearbeiten\ndistance|bool=true|Distanz bearbeiten\nis_track|bool=false|Bahn bearbeiten\npulse|bool=true|Puls &oslash;/max bearbeiten\nkcal|bool=true|Kalorien bearbeiten\nabc|bool=false|Lauf-ABC bearbeiten\ncomment|bool=true|Bemerkung bearbeiten\nroute|bool=true|Strecke bearbeiten\nelevation|bool=false|hm bearbeiten\npartner|bool=false|Trainingspartner bearbeiten\ntemperature|bool=false|Temperatur bearbeiten\nweather|bool=false|Wetter bearbeiten\nclothes|bool=false|Kleidung bearbeiten\nsplits|bool=false|Zwischenzeiten bearbeiten\n', 1, 99),
		array('RunalyzePluginTool_AnalyzeVDOT', 'tool', 'class.RunalyzePluginTool_AnalyzeVDOT.php', 'VDOT analysieren', 'Den VDOT im Zusammenhang mit Wettkampfergebnissen analysieren', '', 1, 99),
		array('RunalyzePluginTool_DbBackup', 'tool', 'class.RunalyzePluginTool_DbBackup.php', 'Datenbank-Import/Export', 'Dieses Plugin sichert die komplette Datenbank und kann ein vorhandenes Backup importieren.', '', 1, 99)
	)
);
$EmptyTables['sport'] = array(
	'columns' => array('name', 'img', 'online', 'short', 'kcal', 'HFavg', 'RPE', 'distances', 'kmh', 'types', 'pulse', 'outside'),
	'values'  => array(
		array('Laufen', 'laufen.gif', 1, 0, 880, 140, 4, 1, 0, 1, 1, 1),
		array('Radfahren', 'radfahren.gif', 1, 0, 770, 120, 2, 1, 1, 0, 1, 1),
		array('Schwimmen', 'schwimmen.gif', 1, 0, 743, 130, 5, 1, 1, 0, 0, 0),
		array('Gymnastik', 'gymnastik.gif', 1, 1, 280, 100, 1, 0, 0, 0, 0, 0),
		array('Sonstiges', 'unknown.gif', 1, 0, 500, 120, 3, 0, 0, 0, 0, 0)
	)
);
$EmptyTables['type'] = array(
	'columns' => array('name', 'abbr', 'RPE', 'splits'),
	'values'  => array(
		array('Dauerlauf', 'DL', 4, 0),
		array('Fahrtspiel', 'FS', 5, 0),
		array('Intervalltraining', 'IT', 7, 1),
		array('Tempodauerlauf', 'TDL', 7, 1),
		array('Wettkampf', 'WK', 10, 1),
		array('Regenerationslauf', 'RL', 2, 0),
		array('Langer Lauf', 'LL', 5, 0),
		array('Warm-/Auslaufen', 'WA', 1, 0)
	)
);