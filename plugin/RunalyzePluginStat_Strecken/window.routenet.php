<?php
/**
 * Window for routenet
 * @package Runalyze\Plugins\Stats
 */
require '../../inc/class.Frontend.php';

use Runalyze\View\Leaflet;
use Runalyze\Model;

$Frontend = new Frontend();

require 'class.RunalyzePluginStat_Strecken.php';
?>

<div class="panel-heading">
	<h1><?php _e('Route network'); ?></h1>
</div>

<div class="panel-content">
<?php
$Routes = DB::getInstance()->query('
	SELECT
		id,
		lats,
		lngs,
		min_lat,
		min_lng,
		max_lat,
		max_lng
	FROM `'.PREFIX.'route`
	WHERE `lats`!=""
	ORDER BY `id` DESC
	LIMIT '.RunalyzePluginStat_Strecken::MAX_ROUTES_ON_NET);

$Map = new Leaflet\Map('map-routenet', 600);

$minLat = 999;
$maxLat = -999;
$minLng = 999;
$maxLng = -999;

while ($RouteData = $Routes->fetch()) {
	$Route = new Model\Route\Object($RouteData);

	$minLat = min($minLat, $RouteData['min_lat']);
	$maxLat = max($maxLat, $RouteData['max_lat']);
	$minLng = min($minLng, $RouteData['min_lng']);
	$maxLng = max($maxLng, $RouteData['max_lng']);

	$Path = new Leaflet\Activity('route-'.$RouteData['id'], $Route, null, false);
	$Path->addOption('hoverable', false);
	$Path->addOption('autofit', false);

	$Map->addRoute($Path);
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
	<?php echo sprintf( __('The network contains your last %s routes.'), RunalyzePluginStat_Strecken::MAX_ROUTES_ON_NET ); ?>
	<?php _e('More routes are not possible at the moment due to performance issues.'); ?>
</p>
</div>