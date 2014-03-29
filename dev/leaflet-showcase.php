<?php
require '../inc/class.Frontend.php';
$Frontend = new Frontend(true);
?><html>
	<head>
		<link rel="stylesheet" href="../lib/less/runalyze-style.css">
		<script src="../lib/min/?g=js"></script>
	</head>
<body>
	<div class="panel">
		<div class="panel-heading">
			<h1>Leaflet Showcase: General map</h1>
		</div>

		<div class="panel-content">
			<!--<div id="map" style="height: 400px;"></div>-->
<script>
//RunalyzeLeaflet.init('map');
</script>
<?php
$Training = new TrainingObject(2428);

$start = microtime(true);

$Map = new LeafletMap('map', 400);
$Map->addRoute( new LeafletTrainingRoute('route', $Training->GpsData()) );
$Map->display();

$end = microtime(true);
$diff = $end - $start; var_dump($diff);
/*$Path = array();
$Info = array();
$DistMarker = array();
$Dist = 1;
$Time = 0;

$GPS = $Training->GpsData();
$GPS->startLoop();
$GPS->setStepSize(5);

while ($GPS->nextStep()) {
	$Path[] = array((float)$GPS->getLatitude(), (float)$GPS->getLongitude());
	$Info[] = array(
		'Distanz'	=> Running::Km($GPS->getDistance(), 2),
		'Zeit'		=> Time::toString($GPS->getTime(), false, 2)
	);

	if (round($GPS->getDistance(), 2) >= $Dist) {
		$DistMarker[] = array(
			'dist'	=> $Dist,
			'pos'	=> json_encode(end($Path)),
			'tooltip'	=> '<strong>'.$Dist.'. km</strong> in '.SportSpeed::minPerKm(1, $GPS->getTime() - $Time).'<br><strong>Zeit:</strong> '.Time::toString($GPS->getTime(), false, 2)
		);
		$Time = $GPS->getTime();
		$Dist += 1;
	}
}

echo '<script>
RunalyzeLeaflet.Routes.addRoute(\'route-2403\', {
	markertopush: [';

foreach ($DistMarker as $Marker) {
	echo '
		L.marker('.$Marker['pos'].', {icon: RunalyzeLeaflet.Routes.distIcon('.$Marker['dist'].'), tooltip: "'.$Marker['tooltip'].'"}),';
}

echo '
		L.marker('.json_encode($Path[0]).', {icon: RunalyzeLeaflet.Routes.startIcon(), tooltip: "Start"}),
		L.marker('.json_encode(end($Path)).', {icon: RunalyzeLeaflet.Routes.endIcon(), tooltip: "<strong>Gesamt:</strong> '.Running::Km($GPS->getDistance(), 2).'<br><strong>Zeit:</strong> '.Time::toString($GPS->getTime()).'"})
	],
	segments: ['.json_encode($Path).'],
	segmentsInfo: ['.json_encode($Info).']
});
</script>';*/
?>
		</div>

		<div class="panel-content">
			<a href="javascript:RunalyzeLeaflet.Routes.toggleAllRoutes();">Toggle routes</a><br>
		</div>
	</div>
</body>
</html>