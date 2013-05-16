<?php
/**
 * Window: plot for calculations
 * @package Runalyze\Plugins\Panels
 */
require '../../inc/class.Frontend.php';

$Frontend = new Frontend();

if (!isset($_GET['y']))
	$_GET['y'] = date("Y");
?>
<h1>Formkurve</h1>

<?php
echo Plot::getDivFor('form'.$_GET['y'], 800, 450);

include FRONTEND_PATH.'../plugin/RunalyzePluginPanel_Rechenspiele/Plot.Form.php';
?>
	<br />
	<br />

<center>
<?php
for ($j = START_YEAR; $j <= date("Y"); $j++) {
	if ($j == $_GET['y'])
		echo '<strong style="margin-right:20px;">'.$j.'</strong>';
	else
		echo Ajax::window('<a href="plugin/RunalyzePluginPanel_Rechenspiele/window.plot.php?y='.$j.'" style="margin-right:20px;">'.$j.'</a>');
}

if ($_GET['y'] == 'all')
	echo '<strong style="margin-right:20px;">Gesamt</strong>';
else
	echo Ajax::window('<a href="plugin/RunalyzePluginPanel_Rechenspiele/window.plot.php?y=all" style="margin-right:20px;">Gesamt</a>');
?>
</center>