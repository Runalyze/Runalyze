<?php
/**
 * Window for weather plots
 * @package Runalyze\Plugins\Stats
 */
require '../../inc/class.Frontend.php';

$Frontend = new Frontend();

if (!isset($_GET['y']))
	$_GET['y'] = date("Y");
?>

<div class="panel-heading">
	<h1>Wetter</h1>
</div>

<div class="panel-content" style="text-align:center;">
<?php
echo Plot::getDivFor('average', 780, 240);

include FRONTEND_PATH.'../plugin/RunalyzePluginStat_Wetter/Plot.Average.php';
?>

	<p>&nbsp;</p>

<?php
echo Plot::getDivFor('year'.$_GET['y'], 780, 240);

include FRONTEND_PATH.'../plugin/RunalyzePluginStat_Wetter/Plot.Year.php';
?>

	<p>&nbsp;</p>

	<p>
<?php
for ($j = START_YEAR; $j <= date("Y"); $j++) {
	if ($j == $_GET['y'])
		echo '<strong style="margin-right:20px;">'.$j.'</strong>';
	else
		echo Ajax::window('<a href="plugin/RunalyzePluginStat_Wetter/window.php?y='.$j.'" style="margin-right:20px;">'.$j.'</a>');
}
?>
	</p>
</div>