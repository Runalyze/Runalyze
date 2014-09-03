<?php
require '../inc/class.Frontend.php';
$Frontend = new Frontend(true);
?><html>
	<head>
		<link rel="stylesheet" href="../lib/less/runalyze-style.css">
		<script src="../build/scripts.js"></script>
	</head>
<body>
	<div class="panel">
		<div class="panel-heading">
			<h1>Leaflet Showcase: General map</h1>
		</div>

		<div class="panel-content">
<?php
$Training = new TrainingObject(2428);

$start = microtime(true);

$Map = new LeafletMap('map', 400);
$Map->addRoute( new LeafletTrainingRoute('route', $Training->GpsData()) );
$Map->display();

$end = microtime(true);
$diff = $end - $start; var_dump($diff);
?>
		</div>

		<div class="panel-content">
			<a href="javascript:RunalyzeLeaflet.Routes.toggleAllRoutes();">Toggle routes</a><br>
		</div>
	</div>
</body>
</html>