<?php
/**
 * Window for routenet
 * @package Runalyze\Plugins\Stats
 */
require '../../inc/class.Frontend.php';

$Frontend = new Frontend();

require 'class.RunalyzePluginStat_Strecken.php';
?>

<div class="panel-heading">
	<h1><?php _e('Route network'); ?></h1>
</div>

<div class="panel-content">
<?php
$AllTrainings = DB::getInstance()->query('
	SELECT
		id,
		time,
		arr_time,
		arr_lat,
		arr_lon,
		arr_alt,
		arr_dist,
		arr_heart,
		arr_pace
	FROM `'.PREFIX.'training`
	WHERE `arr_lat`!=""
	ORDER BY `time` DESC
	LIMIT '.RunalyzePluginStat_Strecken::$MAX_ROUTES_ON_NET)->fetchAll();

$Map = new LeafletMap('map', 600);

$minLat = 999;
$maxLat = -999;
$minLng = 999;
$maxLng = -999;

foreach ($AllTrainings as $Training) {
	$GPS = new GpsData($Training);
	$Bounds = $GPS->getBoundS();

	$minLat = min($minLat, $Bounds['lat.min']);
	$maxLat = max($maxLat, $Bounds['lat.max']);
	$minLng = min($minLng, $Bounds['lng.min']);
	$maxLng = max($maxLng, $Bounds['lng.max']);

	$Route = new LeafletTrainingRoute('route-'.$Training['id'], $GPS, false);
	$Route->addOption('hoverable', false);
	$Route->addOption('autofit', false);

	$Map->addRoute($Route);
}

$Map->setBounds(array(
	'lat.min' => $minLat,
	'lat.max' => $maxLat,
	'lng.min' => $minLng,
	'lng.max' => $maxLng
));
$Map->display();
?>

<p class="info">
	<?php echo sprintf( __('The network contains your last %s routes.'), RunalyzePluginStat_Strecken::$MAX_ROUTES_ON_NET ); ?>
	<?php _e('More routes are due to performance reasons not possible at the moment.'); ?>
</p>
</div>