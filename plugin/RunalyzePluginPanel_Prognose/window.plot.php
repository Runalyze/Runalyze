<?php
/**
 * Window: prognosis plot
 * @package Runalyze\Plugins\Panels
 */
require '../../inc/class.Frontend.php';

$Frontend = new Frontend();
?>
<div class="panel-heading">
	<h1>Prognose-Rechner: Form-Verlauf</h1>
</div>

<div class="panel-content">
<?php
$Plugin = Plugin::getInstanceFor('RunalyzePluginPanel_Prognose');

$vdot = VDOT_FORM;
$distances = $Plugin->getDistances();

if (!isset($_GET['distance'])) {
	if (in_array(10, $distances))
		$distance = 10;
	else
		$distance = trim($distances[0]);
} else
	$distance = $_GET['distance'];

echo Plot::getDivFor('formverlauf_'.str_replace('.', '_', $distance), 800, 450);

include FRONTEND_PATH.'../plugin/RunalyzePluginPanel_Prognose/Plot.Form.php';
?>
	<br />
	<br />

<center>
<?php
foreach ($distances as $km) {
	$km = trim($km);
	$string = Running::Km($km);

	if ($km == $distance)
		echo '<strong style="margin-right:20px;">'.$string.'</strong>';
	else
		echo Ajax::window('<a href="plugin/RunalyzePluginPanel_Prognose/window.plot.php?distance='.$km.'" style="margin-right:20px;">'.$string.'</a>');
}
?>
</center>

<p class="info">
	Zur Erstellung der Prognose wird jeweils der durchschnittliche VDOT-Wert pro Monat betrachtet.
</p>

<p class="info">
	F&uuml;r diese Prognosen wird <strong>kein</strong> Grundlagenausdauer-Faktor verwendet.
</p>
</div>