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

$sport = isset($_GET['sport']) ? (int)$_GET['sport'] : -1;
$year = isset($_GET['y']) ? (int)$_GET['y'] : date('Y');
?>

<div class="panel-heading">
	<?php echo RunalyzePluginStat_Strecken::panelMenuForRoutenet($sport, $year); ?>
	<h1><?php _e('Route network'); ?></h1>
</div>

<div class="panel-content">
<?php
$Conditions = '';

if ($sport > 0) {
	$Conditions .= ' AND `'.PREFIX.'training`.`sportid`='.(int)$sport;
}

if ($year > 0) {
	$Conditions .= ' AND `'.PREFIX.'training`.`time` BETWEEN UNIX_TIMESTAMP(\''.(int)$year.'-01-01\') AND UNIX_TIMESTAMP(\''.((int)$year+1).'-01-01\')-1';
}

if (empty($Conditions)) {
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
		WHERE `lats`!="" '.$Conditions.'
		ORDER BY `id` DESC
		LIMIT '.RunalyzePluginStat_Strecken::MAX_ROUTES_ON_NET);
} else {
	$Routes = DB::getInstance()->query('
		SELECT
			`'.PREFIX.'route`.`id`,
			`'.PREFIX.'route`.`lats`,
			`'.PREFIX.'route`.`lngs`,
			`'.PREFIX.'route`.`min_lat`,
			`'.PREFIX.'route`.`min_lng`,
			`'.PREFIX.'route`.`max_lat`,
			`'.PREFIX.'route`.`max_lng`
		FROM `'.PREFIX.'training`
			LEFT JOIN `'.PREFIX.'route` ON `'.PREFIX.'training`.`routeid`=`'.PREFIX.'route`.`id`
		WHERE `'.PREFIX.'training`.`accountid`='.SessionAccountHandler::getId().' AND`'.PREFIX.'route`.`lats`!="" '.$Conditions.'
		ORDER BY `id` DESC
		LIMIT '.RunalyzePluginStat_Strecken::MAX_ROUTES_ON_NET);
}

$Map = new Leaflet\Map('map-routenet', 600);

$minLat = 90;
$maxLat = -90;
$minLng = 180;
$maxLng = -180;

while ($RouteData = $Routes->fetch()) {
	$Route = new Model\Route\Object($RouteData);

	$minLat = $RouteData['min_lat'] != 0 ? min($minLat, $RouteData['min_lat']) : $minLat;
	$maxLat = $RouteData['max_lat'] != 0 ? max($maxLat, $RouteData['max_lat']) : $maxLat;
	$minLng = $RouteData['min_lng'] != 0 ? min($minLng, $RouteData['min_lng']) : $minLng;
	$maxLng = $RouteData['max_lng'] != 0 ? max($maxLng, $RouteData['max_lng']) : $maxLng;

	$Path = new Leaflet\Activity('route-'.$RouteData['id'], $Route, null, false);
	$Path->addOption('hoverable', false);
	$Path->addOption('autofit', false);

	$Map->addRoute($Path);
}

if (!isset($Route)) {
	echo HTML::error(__('There are no routes matching the criterias.'));
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
	<?php echo sprintf( __('The map contains your %s most recent routes matching the criterias.'), RunalyzePluginStat_Strecken::MAX_ROUTES_ON_NET ); ?>
	<?php _e('More routes are not possible at the moment due to performance issues.'); ?>
</p>
</div>