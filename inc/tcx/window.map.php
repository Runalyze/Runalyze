<?php
/**
 * File the search
 * Call:   inc/tpl/window.search.php
 */
require('../class.Frontend.php');
require_once('class.googleMapsAPI.php');

$Frontend = new Frontend(true, __FILE__);
$Training = new Training($_GET['id']);

$Latitude  = explode(Training::$ARR_SEP, $Training->get('arr_lat'));
$Longitude = explode(Training::$ARR_SEP, $Training->get('arr_lon'));
$Elevation = explode(Training::$ARR_SEP, $Training->get('arr_alt'));
$Time      = explode(Training::$ARR_SEP, $Training->get('arr_time'));
$Distance  = explode(Training::$ARR_SEP, $Training->get('arr_dist'));

$Map = new googleMapsAPI();
$Map->width = "100%";
$Map->height = "100%";
$Map->setPanZoomControl("hide");
$Map->setOverviewMap("hide");
$Map->setScaleControl("hide");
$Map->setMapTypeControl("hide");
$Map->setMapType(CONF_TRAINING_MAPTYPE);
$Map->setContinuousZoom("enabled");
$Map->setDragging("enabled");
$Map->setScrollWheelZoom("enabled");
$Map->setDoubleClickZoom("enabled");
$Map->setGoogleBar("disabled");

function distance($lat1, $lon1, $lat2, $lon2) { 
	$theta = $lon1 - $lon2; 
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
	$dist = acos($dist); 
	$dist = rad2deg($dist); 
	$miles = $dist * 60 * 1.1515;
	$unit = strtoupper($unit);

	return ($miles * 1.609344); // as kilometers
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>Google Maps</title>

		<link rel="stylesheet" type="text/css" href="../../style.css" />
<?php echo $Map->getHeaderScript(); ?>

		<style type="text/css">
		html, body { margin: 0 !important; padding: 0 !important; height: 100% !important; }
		</style>
	</head>

	<body onunload="GUnload();">
		<div style="min-height: 100%;height:100%;">
<?php
$Polylines = array();
$Polylines[] = $Map->addPolyline(CONF_TRAINING_MAP_COLOR, 2, 100);

$km = 0;
$s  = 0;
foreach ($Latitude as $i => $lat) {
	if (floor($Distance[$i]) > $km) {
		$km++;
		$html = $km.'. Kilometer in '.Helper::Time($Time[$i]-$s).'/km<br />';
		$html .= 'H&ouml;he: '.$Elevation[$i].'m<br /><br />';
		$html .= 'Gesamtzeit: '.Helper::Time($Time[$i]);

		if (CONF_TRAINING_MAP_MARKER)
			$Map->addMarker($lat, $Longitude[$i], $html, '', '', $iconImage = '../../img/marker.gif', 'none', 10, 10, 0, 0, 5, 5);

		$s = $Time[$i];

		$Polylines[] = $Map->addPolyline(CONF_TRAINING_MAP_COLOR, 2, 100);
		$Map->addPolylinePoint(end($Polylines), $Latitude[$i-1], $Longitude[$i-1]);
	} elseif ($i > 1 && ($Time[$i]-$Time[$i-1]) <= 1) {
		$dist = distance($Latitude[$i], $Longitude[$i], $Latitude[$i-1], $Longitude[$i-1]);
		if ($dist > 0.050) {
			// Muss eine Pause gewesen sein => Strecke nicht verbinden
			$Polylines[] = $Map->addPolyline(CONF_TRAINING_MAP_COLOR, 2, 100);
		}
	}

	if ($lat != 0 && $Longitude[$i] != 0)
		$Map->addPolylinePoint(end($Polylines), $lat, $Longitude[$i]);
}

echo $Map->getContentElement();
?>
		</div>
	</body>
</html>

<?php
$Frontend->close();
?>