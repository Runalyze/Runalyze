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
	<h1>Streckennetz</h1>
</div>

<div class="panel-content">
<?php
$EmptyMap = new Gmap('all', new GpsData(array(
	'arr_time'	=> '',
	'arr_lat'	=> '',
	'arr_lon'	=> '',
	'arr_alt'	=> '',
	'arr_dist'	=> '',
	'arr_heart'	=> '',
	'arr_pace'	=> ''
)));
$EmptyMap->outputHTML();

echo Ajax::wrapJSasFunction( $EmptyMap->getCodeForInit() );

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

foreach ($AllTrainings as $Training) {
	$Map = new Gmap('all', new GpsData($Training));
	echo Ajax::wrapJSasFunction( $Map->getCodeForPolylines(true) );
}

echo Ajax::wrapJSforDocumentReady('RunalyzeGMap.setOverlayMapToFullscreen();');
?>

<p class="info">
	Das Streckennetz beinhaltet die <?php echo RunalyzePluginStat_Strecken::$MAX_ROUTES_ON_NET; ?> neuesten Strecken.
	Mehr Strecken sind zur Zeit aus Performance-Gr&uuml;nden nicht m&ouml;glich.
</p>
</div>