<?php
/**
 * Draw kilometer per month
 * Call:   inc/draw/kilometer.month.php?y=
 */
require_once '../class.Frontend.php';
$Frontend = new Frontend(true, __FILE__);

$Draw = new Draw(800, 500);

$titleCenter           = 'Monatskilometer '.$_GET['y'];
$titleError            = '';
$Months                = array();
$Kilometers            = array();
$KilometersCompetition = array();
$possibleKM            = 0;
$isDrawable            = true;

$Year = (int)$_GET['y'];
if ($Year >= START_YEAR && $Year <= date('Y') && START_TIME != time()) {
	
	for ($m = 1; $m <= 12; $m++) {
		$Months[]                = Helper::Month($m, true);
		$Kilometers[]            = 0;
		$KilometersCompetition[] = 0;
	}

	$Data = Mysql::getInstance()->fetchAsArray('SELECT (`typid` = '.WK_TYPID.') as `wk`, SUM(`distanz`) as `km`, MONTH(FROM_UNIXTIME(`time`)) as `m` FROM `'.PREFIX.'training` WHERE `sportid`='.RUNNINGSPORT.' AND YEAR(FROM_UNIXTIME(`time`))='.$Year.' GROUP BY (`typid` = '.WK_TYPID.'), MONTH(FROM_UNIXTIME(`time`))');
	foreach ($Data as $dat) {
		if ($dat['wk'] == 1)
			$KilometersCompetition[$dat['m']-1] = $dat['km'];
		else
			$Kilometers[$dat['m']-1] = $dat['km'];
	}

	if (CONF_RECHENSPIELE) {
		$TrimpPerMonth = Helper::TRIMP(0, 365 * Helper::CTL() / 12);
		$AvgMonthPace  = Mysql::getInstance()->fetchSingle('SELECT AVG(`dauer`/60/`distanz`) AS `avg` FROM `'.PREFIX.'training` WHERE `time` > '.(time()-30*DAY_IN_S).' AND `sportid`='.RUNNINGSPORT);
		$possibleKM    = 10 * round($TrimpPerMonth / $AvgMonthPace['avg'] / 10);
	}

	$ScaleFormat    = array(
		"Mode" => SCALE_MODE_ADDALL_START0);
	$TresholdFormat = array(
		"WriteCaption" => TRUE, "Caption" => "Trainingslevel",
		"R" => 180, "G" => 0, "B" => 0, "Alpha" => 50,
		"Ticks" => 0);
	
	$Draw->pData->addPoints($Months, 'Monat');
	$Draw->pData->addPoints($KilometersCompetition, 'Wettkampf-Kilometer');
	$Draw->pData->addPoints($Kilometers, 'Kilometer');
	$Draw->pData->setAxisUnit(0, ' km');
	$Draw->pData->setAbscissa('Monat');
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