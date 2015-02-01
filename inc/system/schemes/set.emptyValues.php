<?php
/**
 * This file contains all empty values needed to create a new account.
 * 
 * Structure:
 * $EmptyTables['TABLENAME_WITHOUT_PREFIX'] = array('columns' => array(...), 'values' => array( array(...), ... )); 
 * 
 * @author Hannes Christiansen
 * @package Runalyze\System
 */
$EmptyTables = array();
$EmptyTables['clothes'] = array(
	'columns' => array('name', 'short', 'order'),
	'values'  => array(
		array(__('Langarmshirt'), __('S-Lang'), 1),
		array(__('T-Shirt'), __('T-Shirt'), 1),
		array(__('Singlet'), __('Singlet'), 1),
		array(__('Jacke'), __('Jacke'), 1),
		array(__('kurze Hose'), __('H-kurz'), 2),
		array(__('lange Hose'), __('H-lang'), 2),
		array(__('Laufshorts'), __('Shorts'), 2),
		array(__('Handschuhe'), __('Handschuhe'), 3),
		array(__('Muetze'), __('Muetze'), 4)
	)
);
$EmptyTables['dataset'] = array(
	'columns' => array('name', 'modus', 'class', 'style', 'position', 'summary', 'summary_mode'),
	'values'  => array(
		array('sportid', 3, '', '', 4, 0, 'YES'),
		array('typeid', 2, '', '', 3, 0, 'NO'),
		array('time', 1, 'c', '', 0, 0, 'NO'),
		array('distance', 2, '', '', 5, 1, 'SUM'),
		array('s', 3, '', '', 6, 1, 'SUM'),
		array('pace', 2, 'small', '', 7, 1, 'AVG'),
		array('elevation', 2, 'small', '', 9, 1, 'SUM'),
		array('kcal', 2, 'small', '', 10, 1, 'SUM'),
		array('pulse_avg', 2, 'small', 'font-style:italic;', 8, 1, 'AVG'),
		array('pulse_max', 1, 'small', '', 0, 0, 'MAX'),
		array('trimp', 2, '', '', 13, 1, 'SUM'),
		array('temperature', 2, 'small', 'width:35px;', 2, 0, 'AVG'),
		array('weatherid', 2, '', '', 1, 0, 'NO'),
		array('route', 1, 'small l', '', 18, 0, 'NO'),
		array('clothes', 1, 'small l', '', 16, 0, 'NO'),
		array('splits', 2, '', '', 11, 0, 'NO'),
		array('comment', 2, 'small l', '', 12, 0, 'NO'),
		array('shoeid', 1, 'small l', '', 0, 0, 'NO'),
		array('vdot', 2, '', '', 14, 1, 'AVG'),
		array('partner', 1, 'small', '', 17, 0, 'NO'),
		array('abc', 1, '', '', 15, 0, 'NO'),
		array('cadence', 1, 'small', '', 19, 1, 'AVG'),
		array('power', 1, 'small', '', 20, 1, 'SUM'),
		array('jd_intensity', 2, '', '', 22, 1, 'SUM')
	)
);
$EmptyTables['plugin'] = array(
	'columns' => array('key', 'type', 'active', 'order'),
	'values'  => array(
		array('RunalyzePluginPanel_Sports', 'panel', 1, 1),
		array('RunalyzePluginPanel_Rechenspiele', 'panel', 1, 2),
		array('RunalyzePluginPanel_Prognose', 'panel', 2, 3),
		array('RunalyzePluginPanel_Schuhe', 'panel', 2, 4),
		array('RunalyzePluginPanel_Sportler', 'panel', 1, 5),
		array('RunalyzePluginStat_Analyse', 'stat', 1, 2),
		array('RunalyzePluginStat_Statistiken', 'stat',1, 1),
		array('RunalyzePluginStat_Wettkampf', 'stat', 1, 3),
		array('RunalyzePluginStat_Wetter', 'stat', 1, 5),
		array('RunalyzePluginStat_Rekorde', 'stat', 2, 6),
		array('RunalyzePluginStat_Strecken', 'stat', 2, 7),
		array('RunalyzePluginStat_Trainingszeiten', 'stat', 2, 8),
		array('RunalyzePluginStat_Trainingspartner', 'stat', 2, 9),
		array('RunalyzePluginStat_Hoehenmeter', 'stat', 2, 10),
		array('RunalyzePluginStat_Laufabc', 'stat', 1, 11),
		array('RunalyzePluginTool_Cacheclean', 'tool', 1, 99),
		array('RunalyzePluginTool_DatenbankCleanup', 'tool', 1, 99),
		array('RunalyzePluginTool_MultiEditor', 'tool', 1, 99),
		array('RunalyzePluginTool_AnalyzeVDOT', 'tool', 1, 99),
		array('RunalyzePluginTool_DbBackup', 'tool', 1, 99),
		array('RunalyzePluginTool_JDTables', 'tool', 1, 99)
	)
);
$EmptyTables['sport'] = array(
	'columns' => array('name', 'img', 'short', 'kcal', 'HFavg', 'RPE', 'distances', 'speed', 'types', 'pulse', 'power', 'outside'),
	'values'  => array(
		array(__('Running'), 'laufen.gif', 0, 880, 140, 4, 1, "min/km", 1, 1, 0, 1, 'RUNNING_SPORT_ID', 'MAIN_SPORT_ID'),
		array(__('Swimming'), 'schwimmen.gif', 0, 743, 130, 5, 1, "min/100m", 0, 0, 0, 0),
		array(__('Biking'), 'radfahren.gif', 0, 770, 120, 2, 1, "km/h", 0, 1, 1, 1),
		array(__('Gymnastics'), 'gymnastik.gif', 1, 280, 100, 1, 0, "", 0, 0, 0, 0),
		array(__('Other'), 'unknown.gif', 0, 500, 120, 3, 0, "", 0, 0, 0, 0)
	)
);
$EmptyTables['type'] = array(
	// Sportid will be updated by AccountHandler::setSpecialConfigValuesFor
	'columns' => array('name', 'abbr', 'RPE', 'sportid'),
	'values'  => array(
		array(__('Jogging'), __('JOG'), 4, 0),
		array(__('Fartlek'), __('FL'), 5, 0),
		array(__('Interval training'), __('IT'), 7, 0),
		array(__('Tempo Run'), __('TR'), 7, 0),
		array(__('Race'), __('RC'), 10, 0, 'TYPE_ID_RACE'),
		array(__('Regeneration Run'), __('RG'), 2, 0),
		array(__('Long Slow Distance'), __('LSD'), 5, 0, 'TYPE_ID_LONGRUN'),
		array(__('Warm-up'), __('WU'), 1, 0)
	)
);