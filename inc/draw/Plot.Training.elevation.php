<?php
/**
 * Draw elevation for a given training
 * Call:   include Plot.Training.elevation.php
 */

if (is_numeric($_GET['id'])) {
	$Training = new Training($_GET['id']);

	if ($Training->hasElevationData()) {
		$Elevations     = array();
		$Elevations_num = array();
		$Distances      = array();
		$Data           = array();
		$Elevation_raw  = explode('|', $Training->get('arr_alt'));
		$Distances_raw  = explode('|', $Training->get('arr_dist'));
		$Distance       = max($Distances_raw);
		$skip           = 0.1;

		for ($i = 0; $i < $Distance; $i += $skip) {
			$Distances[] = $i; //floor($i);
			$Elevations[] = 0;
			$Elevations_num[] = 0;
		}

		for ($i = 0, $n = count($Distances_raw); $i < $n; $i++) {
			$position = floor($Distances_raw[$i] / $skip);

			$Elevations[$position] += $Elevation_raw[$i];
			$Elevations_num[$position]++;
		}

		for ($i = 0, $n = count($Elevations); $i < $n; $i++) {
			if ($Elevations_num[$i] != 0)
				$Elevations[$i] = round($Elevations[$i] / $Elevations_num[$i]);
			elseif ($i == 0)
				$Elevations[$i] = $Elevations[$i+1];
			elseif ($i == count($Elevations)-1)
				$Elevations[$i] = $Elevations[$i-1];
			else
				$Elevations[$i] = ($Elevations[$i-1] + $Elevations[$i+1]) / 2;
		}
	}
}

foreach ($Elevations as $key => $value)
	$Data[(string)($key*$skip)] = $value;

$Plot = new Plot("elevation_".$_GET['id'], 480, 190);
$Plot->Data[] = array('label' => 'H&ouml;he', 'color' => 'rgba(227,217,187,1)', 'data' => $Data);

$Plot->setMarginForGrid(5);
$Plot->addYAxis(1, 'left');
$Plot->addYUnit(1, 'm');
$Plot->setYLimits(1, min($Elevations), max($Elevations), true);
$Plot->setXUnit('km');
$Plot->setLinesFilled();
$Plot->enableTracking();

$Plot->outputJavaScript();
?>