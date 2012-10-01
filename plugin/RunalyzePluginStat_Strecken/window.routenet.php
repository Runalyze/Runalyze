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

$AllTrainings = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'training` WHERE `arr_lat`!="" ORDER BY `id` DESC LIMIT '.RunalyzePluginStat_Strecken::$MAX_ROUTES_ON_NET);
foreach ($AllTrainings as $Training) {
	$Map = new Gmap('all', new GpsData($Training));
	echo Ajax::wrapJSasFunction( $Map->getCodeForPolylines(true) );
}
?>

<p class="info small">
	Das Streckennetz beinhaltet die <?php echo RunalyzePluginStat_Strecken::$MAX_ROUTES_ON_NET; ?> neusten Strecken.
	Mehr Strecken sind zur Zeit aus Performance-Gr&uuml;nden nicht m&ouml;glich.
</p>