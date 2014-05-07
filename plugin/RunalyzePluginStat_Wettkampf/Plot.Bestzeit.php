<?php
/**
 * Draw personal bests for a given distance
 * Call:   include Plot.Bestzeit.php
 */

$Plugin = Plugin::getInstanceFor('RunalyzePluginStat_Wettkampf');

$distance    = !is_numeric($_GET['km']) ? 10 : (float)$_GET['km'];
$Dates       = array();
$Results     = array();
$label       = str_replace('&nbsp;', ' ', sprintf( __('Result over %s'), Running::Km($distance, 1, ($distance <= 3)) ) );
$titleCenter = str_replace('&nbsp;', ' ', sprintf( __('Result overs %s'), Running::Km($distance, 1, ($distance <= 3)) ) );
$timeFormat  = '%M:%S';

$competitions = DB::getInstance()->query('SELECT id,time,s FROM `'.PREFIX.'training` WHERE `typeid`='.CONF_WK_TYPID.' AND `distance`="'.$distance.'" ORDER BY `time` ASC')->fetchAll();
if (!empty($competitions)) {
	foreach ($competitions as $competition) {
		if (!$Plugin->isFunCompetition($competition['id'])) {
			$Dates[]   = $competition['time'];
			$Results[$competition['time'].'000'] = ($competition['s']*1000); // Attention: timestamp(0) => 1:00:00
		}
	}

	if (max($Results) > 3600*1000)
		$timeFormat = '%H:%M:%S';
}

$Plot = new Plot("bestzeit".$distance*1000, 480, 190);
$Plot->Data[] = array('label' => $label, 'data' => $Results);

$Plot->setMarginForGrid(5);
$Plot->setXAxisAsTime();

if (count($Results) == 1)
	$Plot->setXAxisTimeFormat('%d.%m.%y');

$Plot->addYAxis(1, 'left');
$Plot->setYAxisAsTime(1);
$Plot->setYAxisTimeFormat($timeFormat, 1);

$Plot->lineWithPoints();
$Plot->enableTracking();
$Plot->hideLegend();
$Plot->setTitle($titleCenter);

$Plot->outputJavaScript();