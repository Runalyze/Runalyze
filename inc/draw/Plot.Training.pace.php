<?php
/**
 * Draw pace for a given training
 * Call:   include Plot.Training.pace.php
 */

if (is_numeric($_GET['id'])) {
	$Training = new Training($_GET['id']);

	if ($Training->hasPaceData()) {
		$Distances      = array();
		$Data           = array();
		$Paces          = array();
		$Paces_num      = array();
		$Paces_raw      = explode('|', $Training->get('arr_pace'));
		$Distances_raw  = explode('|', $Training->get('arr_dist'));
		$Distance       = max($Distances_raw);
		$skip           = 0.1;

		for ($i = 0; $i < $Distance; $i += $skip) {
			$Distances[] = $i; //floor($i);
			$Paces[] = 0;
			$Paces_num[] = 0;
		}

		for ($i = 0, $n = count($Distances_raw); $i < $n; $i++) {
			$position = floor($Distances_raw[$i] / $skip);

			if ($Paces_raw[$i] > 60) {
				$Paces[$position] += $Paces_raw[$i]*1000;
				$Paces_num[$position]++;
			}
		}

		for ($i = 0, $n = count($Paces); $i < $n; $i++) {
			if ($Paces_num[$i] != 0)
				$Paces[$i] = round($Paces[$i] / $Paces_num[$i]);
			elseif ($i == 0)
				$Paces[$i] = $Paces[$i+1];
			elseif ($i == count($Paces)-1)
				$Paces[$i] = $Paces[$i-1];
			else
				$Paces[$i] = ($Paces[$i-1] + $Paces[$i+1]) / 2;
		}
	}
}


foreach ($Paces as $key => $value)
	$Data[(string)($key*$skip)] = $value;

$Plot = new Plot("pace_".$_GET['id'], 480, 190);
$Plot->Data[] = array('label' => 'Pace', 'color' => 'rgb(0,0,136)', 'data' => $Data);

$Plot->setMarginForGrid(5);
$Plot->setYAxisTimeFormat('%M:%S');
$Plot->setXUnit('km');
$Plot->enableTracking();

$Plot->outputJavaScript();
?>