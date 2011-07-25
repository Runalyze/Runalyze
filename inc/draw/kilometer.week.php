<?php
/**
 * Draw kilometer per week
 * Call:   inc/draw/kilometer.week.php?y=
 */
require_once '../class.Frontend.php';
$Frontend = new Frontend(true, __FILE__);

$Draw = new Draw(800, 500);

$titleCenter           = 'Wochenkilometer '.$_GET['y'];
$titleError            = '';
$Weeks                 = array();
$Kilometers            = array();
$KilometersCompetition = array();
$possibleKM            = 0;
$isDrawable            = true;

$Year = (int)$_GET['y'];
if ($Year >= START_YEAR && $Year <= date('Y') && START_TIME != time()) {
	
	for ($w = 0; $w <= 53; $w++) {
		$Weeks[]                 = $w;
		$Kilometers[]            = 0;
		$KilometersCompetition[] = 0;
	}

	$Data = Mysql::getInstance()->fetchAsArray('SELECT (`typid` = '.WK_TYPID.') as `wk`, SUM(`distanz`) as `km`, WEEK(FROM_UNIXTIME(`time`),1) as `w` FROM `'.PREFIX.'training` WHERE `sportid`='.RUNNINGSPORT.' AND YEAR(FROM_UNIXTIME(`time`))='.$Year.' GROUP BY (`typid` = '.WK_TYPID.'), WEEK(FROM_UNIXTIME(`time`),1)');
	foreach ($Data as $dat) {
		if ($dat['wk'] == 1)
			$KilometersCompetition[$dat['w']] = $dat['km'];
		else
			$Kilometers[$dat['w']] = $dat['km'];
	}

	if (CONF_RECHENSPIELE) {
		$TrimpPerMonth = Helper::TRIMP(0, 7 * Helper::CTL());
		$AvgMonthPace  = Mysql::getInstance()->fetchSingle('SELECT AVG(`dauer`/60/`distanz`) AS `avg` FROM `'.PREFIX.'training` WHERE `time` > '.(time()-30*DAY_IN_S).' AND `sportid`='.RUNNINGSPORT);
		$possibleKM    = 5 * round($TrimpPerMonth / $AvgMonthPace['avg'] / 5);
	}

	$ScaleFormat    = array(
		"Mode" => SCALE_MODE_ADDALL_START0);
	$TresholdFormat = array(
		"WriteCaption" => TRUE, "Caption" => "Trainingslevel",
		"R" => 180, "G" => 0, "B" => 0, "Alpha" => 50,
		"Ticks" => 0);
	
	$Draw->pData->addPoints($Weeks, 'Woche');
	$Draw->pData->addPoints($KilometersCompetition, 'Wettkampf-Kilometer');
	$Draw->pData->addPoints($Kilometers, 'Kilometer');
	$Draw->pData->setAxisUnit(0, ' km');
	$Draw->pData->setAbscissa('Woche');
} else {
	$isDrawable = false;
	$titleError = 'Das Jahr liegt au&#223;erhalb meiner M&#246;glichkeiten.';
}

$Draw->startImage();

if ($isDrawable) {
	$Draw->drawScale($ScaleFormat);
	$Draw->drawStackedBarChart();
	
	if ($possibleKM > 0)
		$Draw->pImage->drawThreshold($possibleKM, $TresholdFormat);
}

$Draw->drawCenterTitle($titleCenter);

if ($titleError != '')
	$Draw->drawCenteredTitle($titleError);

$Draw->finish();
?>