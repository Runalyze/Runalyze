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
		array(__('long sleeve'), __('l-sleeve'), 1),
		array(__('T-shirt'), __('T-Shirt'), 1),
		array(__('singlet'), __('singlet'), 1),
		array(__('jacket'), __('jacket'), 1),
		array(__('long pants'), __('l-pants'), 2),
		array(__('shorts'), __('shorts'), 2),
		array(__('gloves'), __('gloves'), 3),
		array(__('hat'), __('hat'), 4)
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
		array('elevation', 2, 'small', '', 10, 1, 'SUM'),
		array('kcal', 2, 'small', '', 11, 1, 'SUM'),
		array('pulse_avg', 2, 'small', 'font-style:italic;', 8, 1, 'AVG'),
		array('pulse_max', 1, 'small', '', 9, 0, 'MAX'),
		array('trimp', 2, '', '', 14, 1, 'SUM'),
		array('temperature', 2, 'small', 'width:35px;', 2, 0, 'AVG'),
		array('weatherid', 2, '', '', 1, 0, 'NO'),
		array('routeid', 1, 'small l', '', 20, 0, 'NO'),
		array('clothes', 1, 'small l', '', 17, 0, 'NO'),
		array('splits', 2, '', '', 12, 0, 'NO'),
		array('comment', 2, 'small l', '', 13, 0, 'NO'),
		array('shoeid', 1, 'small l', '', 18, 0, 'NO'),
		array('vdot', 2, '', '', 15, 1, 'AVG'),
		array('partner', 1, 'small', '', 19, 0, 'NO'),
		array('abc', 1, '', '', 16, 0, 'NO'),
		array('cadence', 1, 'small', '', 21, 1, 'AVG'),
		array('power', 1, 'small', '', 22, 1, 'SUM'),
		array('jd_intensity', 2, '', '', 23, 1, 'SUM'),
		array('groundcontact', 2, 'small', '', 24, 1, 'AVG'),
		array('vertical_oscillation', 2, 'small', '', 25, 1, 'AVG')
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
		array('RunalyzePluginTool_JDTables', 'tool', 1, 99),
		array('RunalyzePluginPanel_Ziele', 'panel', 0, 6)
	)
);
$EmptyTables['sport'] = array(
	'columns' => array('name', 'img', 'short', 'kcal', 'HFavg', 'distances', 'speed', 'types', 'power', 'outside'),
	'values'  => array(
		array(__('Running'), 'icons8-running', 0, 880, 140, 1, "min/km", 1, 0, 1, 'RUNNING_SPORT_ID', 'MAIN_SPORT_ID'),
		array(__('Swimming'), 'icons8-swimming', 0, 743, 130, 1, "min/100m", 0, 0, 0),
		array(__('Biking'), 'icons8-regular_biking', 0, 770, 120, 1, "km/h", 0, 1, 1),
		array(__('Gymnastics'), 'icons8-yoga', 1, 280, 100, 0, "km/h", 0, 0, 0),
		array(__('Other'), 'icons8-sports_mode', 0, 500, 120, 0, "km/h", 0, 0, 0)
	)
);
$EmptyTables['type'] = array(
	// Sportid will be updated by AccountHandler::setSpecialConfigValuesFor
	'columns' => array('name', 'abbr', 'hr_avg', 'quality_session'),
	'values'  => array(
		array(__('Jogging'), __('JOG'), 143, 0),
		array(__('Fartlek'), __('FL'), 150, 1),
		array(__('Interval training'), __('IT'), 165, 1),
		array(__('Tempo Run'), __('TR'), 165, 1),
		array(__('Race'), __('RC'), 190, 1, 'TYPE_ID_RACE'),
		array(__('Regeneration Run'), __('RG'), 128, 0),
		array(__('Long Slow Distance'), __('LSD'), 150, 1),
		array(__('Warm-up'), __('WU'), 128, 0)
	)
);
