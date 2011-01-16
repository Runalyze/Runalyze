<?php
require_once('googleMapsAPI.inc.php');
include('../../config/functions.php');
connect();

$dat_db = mysql_query('SELECT `arr_lat`, `arr_lon` FROM `ltb_training` WHERE `id`='.$_GET['id'].' LIMIT 1');
if (mysql_num_rows($dat_db) == 0)
	die('No latitude/longitude found for ID='.$_GET['id']);
$dat = mysql_fetch_assoc($dat_db);

$latitude = explode('|', $dat['arr_lat']);
$longitude = explode('|', $dat['arr_lon']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>Google Maps</title>
<?php
// KARTE
$googlemaps = new xmlgooglemaps_googleMapAPI("");
$googlemaps->width = "100%";
$googlemaps->height = "100%";
$googlemaps->setPanZoomControl("hide");
$googlemaps->setOverviewMap("hide");
$googlemaps->setScaleControl("hide");
$googlemaps->setMapTypeControl("hide");
$googlemaps->setMapType("G_HYBRID_MAP");
$googlemaps->setContinuousZoom("enabled");
$googlemaps->setDragging("enabled");
$googlemaps->setScrollWheelZoom("enabled");
$googlemaps->setDoubleClickZoom("enabled");
$googlemaps->setGoogleBar("disabled");
echo($googlemaps->getHeaderScript());
?>
</head>
<body style="margin: 0; overflow: hidden; height: 300px;" onunload="GUnload();">
<?php
//Tracks
$pl = $googlemaps->addPolyline("#FF5500",2,100);
foreach ($latitude as $i => $lat)
	if ($lat != 0 && $longitude[$i] != 0)
		$googlemaps->addPolylinePoint($pl,$lat,$longitude[$i]);
echo($googlemaps->getContentElement());
?>
</body>
</html>