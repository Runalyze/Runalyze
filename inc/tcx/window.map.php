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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>Google Maps</title>

		<link rel="stylesheet" type="text/css" href="../../style.css" />
<?php echo $Map->getHeaderScript(); ?>
	</head>

	<body style="margin: 0; overflow: hidden; height: 300px;" onunload="GUnload();">

<?php
$Polyline = $Map->addPolyline(CONF_TRAINING_MAP_COLOR, 2, 100);

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
	}

	if ($lat != 0 && $Longitude[$i] != 0)
		$Map->addPolylinePoint($Polyline, $lat, $Longitude[$i]);
}

echo $Map->getContentElement();
?>

	</body>
</html>

<?php
$Frontend->close();
?>