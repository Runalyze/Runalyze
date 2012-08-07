<?php
require '../../inc/class.Frontend.php';

new Frontend();
?>
<h1>Streckennetz (max. 50 Strecken)</h1>

<?php
$EmptyMap = new Gmap('all', array());
$EmptyMap->outputHTML();

echo Ajax::wrapJSasFunction( $EmptyMap->getCodeForInit() );

$AllTrainings = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'training` WHERE `arr_lat`!="" ORDER BY `id` DESC LIMIT 50');
foreach ($AllTrainings as $Training) {
	$Map = new Gmap('all', new GpsData($Training));
	echo Ajax::wrapJSasFunction( $Map->getCodeForPolylines(true) );
}
?>