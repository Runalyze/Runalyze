<?php
/**
 * Draw heartrate for a given training
 * Call:   include Plot.Training.pulse.php
 */

if (is_numeric($_GET['id'])) {
	$Training = new Training($_GET['id']);

	if ($Training->hasPulseData()) {
		$Data           = array();
		$Distances      = array();
		$HR             = array();
		$HR_num         = array();
		$HR_raw         = explode('|', $Training->get('arr_heart'));
		$Distances_raw  = explode('|', $Training->get('arr_dist'));
		$Distance       = max($Distances_raw);
		$skip           = 0.1;

		for ($i = 0; $i < $Distance; $i += $skip) {
			$Distances[] = $i; //floor($i);
			$HR[] = 0;
			$HR_num[] = 0;
		}

		for ($i = 0, $n = count($Distances_raw); $i < $n; $i++) {
			$position = floor($Distances_raw[$i] / $skip);

			if ($HR_raw[$i] > 60) {
				$HR[$position] += round(100 * $HR_raw[$i] / HF_MAX);
				$HR_num[$position]++;
			}
		}

		for ($i = 0, $n = count($HR); $i < $n; $i++) {
			if ($HR_num[$i] != 0)
				$HR[$i] = round($HR[$i] / $HR_num[$i]);
			elseif ($i == 0)
				$HR[$i] = $HR[$i+1];
			elseif ($i == count($HR)-1)
				$HR[$i] = $HR[$i-1];
			else
				$HR[$i] = ($HR[$i-1] + $HR[$i+1]) / 2;
		}
	}
}

foreach ($HR as $key => $value)
	$Data[(string)($key*$skip)] = $value;

$Plot = new Plot("pulse_".$_GET['id'], 480, 190);
$Plot->Data[] = array('label' => 'Herzfrequenz', 'color' => 'rgb(136,0,0)', 'data' => $Data);

$Plot->setMarginForGrid(5);
$Plot->addYAxis(1, 'left');
$Plot->addYUnit(1, '%');
$Plot->setYTicks(1, 5, 0);
$Plot->setYLimits(1, 50, 100);
$Plot->setXUnit('km');
$Plot->enableTracking();

// TODO: Zonen
// TODO: average

$Plot->outputJavaScript();
?>