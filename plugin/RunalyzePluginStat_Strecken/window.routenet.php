<?php
require '../../inc/class.Frontend.php';

$Frontend = new Frontend();

require 'class.RunalyzePluginStat_Strecken.php';
?>
<h1>Streckennetz</h1>

<?php
$EmptyMap = new Gmap('all', array());
$EmptyMap->outputHTML();

echo Ajax::wrapJSasFunction( $EmptyMap->getCodeForInit() );

$AllTrainings = Mysql::getInstance()->fetchAsArray('
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
	ORDER BY `id` DESC
	LIMIT '.RunalyzePluginStat_Strecken::$MAX_ROUTES_ON_NET);
foreach ($AllTrainings as $Training) {
	$Map = new Gmap('all', new GpsData($Training));
	echo Ajax::wrapJSasFunction( $Map->getCodeForPolylines(true) );
}

echo Ajax::wrapJSasFunction('$("#map_all").height($(window).height()*0.8);');
?>

<p class="info small">
	Das Streckennetz beinhaltet die <?php echo RunalyzePluginStat_Strecken::$MAX_ROUTES_ON_NET; ?> neusten Strecken.
	Mehr Strecken sind zur Zeit aus Performance-Gr&uuml;nden nicht m&ouml;glich.
</p>