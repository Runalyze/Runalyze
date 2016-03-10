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
$EmptyTables['equipment_type'] = array(
	'columns' => array('name', 'input'),
	'values'  => array(
		array(__('Shoes'), 0, 'EQUIPMENT_SHOES_ID'),
		array(__('Clothes'), 1, 'EQUIPMENT_CLOTHES_ID')
	)
);

$EmptyTables['plugin'] = array(
	'columns' => array('key', 'type', 'active', 'order'),
	'values'  => array(
		array('RunalyzePluginPanel_Sports', 'panel', 1, 1),
		array('RunalyzePluginPanel_Rechenspiele', 'panel', 1, 2),
		array('RunalyzePluginPanel_Prognose', 'panel', 2, 3),
		array('RunalyzePluginPanel_Equipment', 'panel', 2, 4),
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
		array('RunalyzePluginStat_Tag', 'stat', 1, 11),
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
	'columns' => array('name', 'img', 'short', 'kcal', 'HFavg', 'distances', 'speed', 'power', 'outside'),
	'values'  => array(
		array(__('Running'), 'icons8-running', 0, 880, 140, 1, "min/km", 0, 1, 'RUNNING_SPORT_ID', 'MAIN_SPORT_ID'),
		array(__('Swimming'), 'icons8-swimming', 0, 743, 130, 1, "min/100m", 0, 0),
		array(__('Biking'), 'icons8-regular_biking', 0, 770, 120, 1, "km/h", 1, 1),
		array(__('Gymnastics'), 'icons8-yoga', 1, 280, 100, 0, "km/h", 0, 0),
		array(__('Other'), 'icons8-sports_mode', 0, 500, 120, 0, "km/h", 0, 0)
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
