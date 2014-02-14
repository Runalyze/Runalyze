<?php
/**
 * Window: prognosis plot
 * @package Runalyze\Plugins\Panels
 */
require '../../inc/class.Frontend.php';

$Frontend = new Frontend();

$Plugin    = Plugin::getInstanceFor('RunalyzePluginPanel_Prognose');
$distances = $Plugin->getDistances();

if (!isset($_GET['distance'])) {
	$distance = (in_array(10, $distances)) ? 10 : trim($distances[0]);
} else {
	$distance = (float)$_GET['distance'];
}

$Submenu = '';
foreach ($distances as $km) {
	$km = trim($km);
	$link = 'plugin/RunalyzePluginPanel_Prognose/window.plot.php?distance='.$km;
	$Submenu .= '<li'.($km == $distance ? ' class="active"' : '').'>'.Ajax::window('<a href="'.$link.'">'.Running::Km($km).'</a>').'</li>';
}
?>
<div class="panel-heading">
	<div class="panel-menu">
		<ul>
			<li class="with-submenu"><span class="link">Distanz w&auml;hlen</span><ul class="submenu"><?php echo $Submenu; ?></ul></li>
		</ul>
	</div>
	<h1>Prognose-Rechner: Form-Verlauf</h1>
</div>

<div class="panel-content">
	<?php
	echo Plot::getDivFor('formverlauf_'.str_replace('.', '_', $distance), 800, 450);
	include FRONTEND_PATH.'../plugin/RunalyzePluginPanel_Prognose/Plot.Form.php';
	?>

	<p class="info">
		Zur Erstellung der Prognose wird jeweils der durchschnittliche VDOT-Wert pro Monat betrachtet.
	</p>

	<p class="info">
		F&uuml;r diese Prognosen wird <strong>kein</strong> Grundlagenausdauer-Faktor verwendet.
	</p>
</div>