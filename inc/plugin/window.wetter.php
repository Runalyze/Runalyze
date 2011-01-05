<?php
/**
 * File for displaying statistic plugins.
 * Call:   inc/plugin/window.wetter.php
 */
require('../class.Frontend.php');
$Frontend = new Frontend(true, __FILE__);
$Frontend->displayHeader();

// TODO Get new draw-file with draw-class
?>
<h1>Wetter</h1>

<center>
<img id="average" height="242" width="782" src="lib/draw/weather.php?all=all&m=m" />
<br />

<?php
for ($j = START_YEAR; $j <= date("Y"); $j++)
	echo NL.Ajax::imgChange('<a href="lib/draw/weather.php?y='.$j.'&m=m" style="margin-right:20px;">'.$j.'</a>','average');
echo NL.Ajax::imgChange('<a href="lib/draw/weather.php?all=all&m=m" style="margin-right:20px;">Gesamt</a>','average');
?>
<br />
<br />
<img id="all" height="242" width="782" src="lib/draw/weather.php?y=<?php echo date("Y"); ?>" />
<br />

<?php
for ($j = START_YEAR; $j <= date("Y"); $j++)
	echo NL.Ajax::imgChange('<a href="lib/draw/weather.php?y='.$j.'" style="margin-right:20px;">'.$j.'</a>','all');
?>
</center>
<?php
$Frontend->displayFooter();
$Frontend->close();
?>