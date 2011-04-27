<?php
/**
 * Draw analyse of training: ATL/CTL/VDOT
 * Call:   inc/draw/plugin.rechenspiele.php
 */
require_once '../class.Frontend.php';
$Frontend = new Frontend(true, __FILE__);

$Draw = new Draw(800, 450);
$Draw->padding['left']  = 45;
$Draw->padding['right'] = 30;

$titleCenter = 'Formkurve '.$_GET['y'];
$titleError  = '';
$isDrawable  = true;
$ATLs        = array();
$CTLs        = array();
$VDOTs       = array();
$Trimps_raw  = array();
$VDOTs_raw   = array();

$Year = (int)$_GET['y'];
if ($Year >= START_YEAR && $Year <= date('Y')) {
	for ($d = 1; $d <= 366; $d++) {
		$Months[] = Helper::Month(ceil(12*$d/366), true);
		$ATLs[]   = VOID;
		$CTLs[]   = VOID;
		$VDOTs[]  = VOID;
		$Trimps_raw[] = 0;
		$VDOTs_raw[]  = 0;
	}

	for ($i = 0; $i < CTL_DAYS; $i++)
		$Trimps_raw[] = 0;
	for ($i = 0; $i < 30;       $i++)
		$VDOTs_raw[]  = 0;

	// Here ATL/CTL/VDOT will be implemented again
	// Normal functions are too slow, calling them for each day would trigger each time a query
	// - ATL/CTL: SUM(`trimp`) for ATL_DAYS / CTL_DAYS
	// - VDOT: AVG(`vdot`) der letzten 30 Tage

	$Data = Mysql::getInstance()->fetchAsArray('
		SELECT
			YEAR(FROM_UNIXTIME(`time`)) as `y`,
			DAYOFYEAR(FROM_UNIXTIME(`time`)) as `d`,
			AVG(`trimp`) as `trimp`
		FROM `ltb_training`
		WHERE
			YEAR(FROM_UNIXTIME(`time`))='.$Year.' OR (
				YEAR(FROM_UNIXTIME(`time`))='.($Year-1).' AND
				DAYOFYEAR(FROM_UNIXTIME(`time`)) >= '.(366-CTL_DAYS).'
			)
		GROUP BY `y`, `d`');

	foreach ($Data as $dat) {
		$index = $dat['d'] - (366 - CTL_DAYS);
		if ($dat['y'] == $Year)
			$index += 366;

		$Trimps_raw[$index] = $dat['trimp'];
	}

	$Data = Mysql::getInstance()->fetchAsArray('
		SELECT
			YEAR(FROM_UNIXTIME(`time`)) as `y`,
			DAYOFYEAR(FROM_UNIXTIME(`time`)) as `d`,
			`id`,
			`vdot`
		FROM `ltb_training`
		WHERE
			`vdot`>0 AND (
				YEAR(FROM_UNIXTIME(`time`))='.$Year.' OR (
					YEAR(FROM_UNIXTIME(`time`))='.($Year-1).' AND
					DAYOFYEAR(FROM_UNIXTIME(`time`)) >= '.(366-VDOT_DAYS).'
				)
			)
		GROUP BY `y`, `d`');
	foreach ($Data as $dat) {
		$index = $dat['d'] - (366 - 30);
		if ($dat['y'] == $Year)
			$index += 366;

		$VDOTs_raw[$index] = $dat['vdot'];
	}

	$max = 366;
	if ($Year == date('Y'))
		$max = date('z');

	for ($d = 1; $d <= $max; $d++) {
		$ATLs[$d-1]   = 100 * array_sum(array_slice($Trimps_raw, CTL_DAYS + $d - ATL_DAYS, ATL_DAYS)) / ATL_DAYS / CONFIG_MAX_ATL;
		$CTLs[$d-1]   = 100 * array_sum(array_slice($Trimps_raw, CTL_DAYS + $d - CTL_DAYS, CTL_DAYS)) / CTL_DAYS / CONFIG_MAX_CTL;

		$VDOT_slice = array_slice($VDOTs_raw, VDOT_DAYS + $d - VDOT_DAYS, VDOT_DAYS);
		$VDOT_num_data = array_count_values($VDOT_slice);
		$VDOT_num_zero = $VDOT_num_data[0];
		$VDOT_sum = array_sum($VDOT_slice);
		$VDOT_num = count($VDOT_slice) - $VDOT_num_zero;
		if (count($VDOT_slice) != 0 && $VDOT_num != 0)
			$VDOTs[$d-1]  = JD::correctVDOT($VDOT_sum / $VDOT_num);
	}

} else {
	$isDrawable = false;
	$titleError = 'Das Jahr liegt au&#223;erhalb meiner M&#246;glichkeiten.';
}

if (empty($ATLs) && empty($CTLs) && empty($VDOTs)) {
	$isDrawable = false;
	$titleError = 'F&#252;r dieses Jahr liegen keine Daten vor.';
}

if ($isDrawable) {
	if ($Year == (int)date('Y'))
		$minVDOT = min( array_slice($VDOTs, 0, date('z')) );
	else
		$minVDOT = min( $VDOTs );
	$maxVDOT = max( $VDOTs );

	$ScaleFormat    = array(
		"XMargin" => 0,
		"DrawYLines" => array(1),
		"TickAlpha" => 50,
		"Mode" => SCALE_MODE_MANUAL,
		"ManualScale" => array(0 => array(
			"Min" => 0,
			"Max" => 100), 1 => array(
			"Min" => floor($minVDOT/5)*5,
			"Max" =>  ceil($maxVDOT/5)*5)),
		"LabelingMethod" => LABELING_DIFFERENT);
	$LegendFormat   = array("Style" => LEGEND_NOBORDER, "Mode" => LEGEND_VERTICAL);

	if (max($CTLs) > VOID) {
		$CTLsFormat = array("R" => 0, "G" => 136, "B" => 0);
		$Draw->pData->addPoints($CTLs, 'Form (CTL)');
		$Draw->pData->setSerieOnAxis('Form (CTL)', 0);
		$Draw->pData->setPalette('Form (CTL)', $CTLsFormat);
	}

	if (max($ATLs) > VOID) {
		$ATLsFormat = array("R" => 136, "G" => 0, "B" => 0);
		$Draw->pData->addPoints($ATLs, 'M&#252;digkeit (ATL)');
		$Draw->pData->setSerieOnAxis('M&#252;digkeit (ATL)', 0);
		$Draw->pData->setPalette('M&#252;digkeit (ATL)', $ATLsFormat);
	}

	if (max($VDOTs) > VOID) {
		$VDOTsFormat = array("R" => 0, "G" => 0, "B" => 0);
		$Draw->pData->addPoints($VDOTs, 'VDOT');
		$Draw->pData->setSerieOnAxis('VDOT', 1);
		$Draw->pData->setPalette('VDOT', $VDOTsFormat);
	}
	
	$Draw->pData->setAxisPosition(1, AXIS_POSITION_RIGHT);
	$Draw->pData->setAxisUnit(0, ' %');
	$Draw->pData->addPoints($Months, 'Monate');
	$Draw->pData->setAbscissa('Monate');
	$Draw->pData->setAxisName(0, 'CTL/ATL');
	$Draw->pData->setAxisName(1, 'VDOT');
}

$Draw->setCaching(false);
$Draw->startImage();
$Draw->drawCenterTitle($titleCenter);

if ($isDrawable) {
	$Draw->drawScale($ScaleFormat);
	
	$Draw->pData->setSerieDrawable('Form (CTL)', TRUE);
	$Draw->pData->setSerieDrawable('M&#252;digkeit (ATL)', FALSE);
	$Draw->pData->setSerieDrawable('VDOT', FALSE);
	$Draw->drawAreaChart();
	
	$Draw->pData->setSerieDrawable('Form (CTL)', FALSE);
	$Draw->pData->setSerieDrawable('M&#252;digkeit (ATL)', TRUE);
	$Draw->pData->setSerieDrawable('VDOT', TRUE);
	$Draw->pImage->drawLineChart(array("BreakVoid" => TRUE));

	$Draw->pData->setSerieDrawable('Form (CTL)', TRUE);
	$Draw->pImage->drawLegend(50, 15, $LegendFormat);
}

if ($titleError != '')
	$Draw->drawCenteredTitle($titleError);

$Draw->finish();
?>