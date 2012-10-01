<?php
require '../../inc/class.Frontend.php';

$Frontend = new Frontend();

if (!isset($_GET['y']))
	$_GET['y'] = date("Y");
?>
<h1>Wetter</h1>

<center>

<?php
echo Plot::getDivFor('average', 780, 240);

include FRONTEND_PATH.'../plugin/RunalyzePluginStat_Wetter/Plot.Average.php';
?>
	<br />
	<br />
<?php
echo Plot::getDivFor('year'.$_GET['y'], 780, 240);

include FRONTEND_PATH.'../plugin/RunalyzePluginStat_Wetter/Plot.Year.php';
?>
	<br />
	
<?php
for ($j = START_YEAR; $j <= date("Y"); $j++) {
	if ($j == $_GET['y'])
		echo '<strong style="margin-right:20px;">'.$j.'</strong>';
	else
		echo Ajax::window('<a href="plugin/RunalyzePluginStat_Wetter/window.php?y='.$j.'" style="margin-right:20px;">'.$j.'</a>');
}
?>
</center>