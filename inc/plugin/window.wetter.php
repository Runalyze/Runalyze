<?php
/**
 * File for displaying statistic plugins.
 * Call:   inc/plugin/window.wetter.php
 */
require('../class.Frontend.php');
$Frontend = new Frontend(true, __FILE__);
$Frontend->displayHeader();
?>
<h1>Wetter</h1>

<center>
<div class="bigImg" style="height:240px; width:780px;"><img id="average" height="240" width="780" src="inc/draw/plugin.wetter.php?all=all&m=m" /></div>
<br />

<?php
for ($j = START_YEAR; $j <= date("Y"); $j++)
	echo NL.Ajax::imgChange('<a href="inc/draw/plugin.wetter.php?y='.$j.'&m=m" style="margin-right:20px;">'.$j.'</a>','average');
echo NL.Ajax::imgChange('<a href="inc/draw/plugin.wetter.php?all=all&m=m" style="margin-right:20px;">Gesamt</a>','average');
?>
<br />
<br />
<div class="bigImg" style="height:240px; width:780px;"><img id="all" height="240" width="780" src="inc/draw/plugin.wetter.php?y=<?php echo date("Y"); ?>" /></div>
<br />

<?php
for ($j = START_YEAR; $j <= date("Y"); $j++)
	echo NL.Ajax::imgChange('<a href="inc/draw/plugin.wetter.php?y='.$j.'" style="margin-right:20px;">'.$j.'</a>','all');
?>
</center>
<?php
$Frontend->displayFooter();
$Frontend->close();
?>