<?php
/**
 * Draw personal bests for a given distance
 * Call:   include Plot.Bestzeit.php
 */

$Plugin = Plugin::getInstanceFor('RunalyzePluginStat_Wettkampf');

$distance    = !is_numeric($_GET['km']) ? 10 : $_GET['km'];
$Dates       = array();
$Results     = array();
$titleCenter = 'Bestzeiten &#252;ber '.Running::Km($distance, 1, ($distance <= 3));
$timeFormat  = '%M:%S';

$titleCenter = str_replace('&nbsp;', ' ', $titleCenter);

$competitions = Mysql::getInstance()->fetchAsArray('SELECT id,time,s FROM `'.PREFIX.'training` WHERE `typeid`='.CONF_WK_TYPID.' AND `distance`="'.$distance.'" ORDER BY `time` ASC');
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
$Plot->Data[] = array('label' => $titleCenter, 'data' => $Results);

$Plot->setMarginForGrid(5);
$Plot->setXAxisAsTime();
$Plot->setYAxisTimeFormat($timeFormat);

if (count($Results) == 1)
	$Plot->setXAxisTimeFormat('%d.%m.%y');

$Plot->lineWithPoints();
$Plot->enableTracking();

$Plot->outputJavaScript();
?>