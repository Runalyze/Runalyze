<?php
/**
 * File for displaying the kilometers of each month.
 * Call:   inc/plugin/window.monatskilometer.php
 */
require('../class.Frontend.php');
$Frontend = new Frontend(true, __FILE__);
$Frontend->displayHeader();

// TODO Get new draw-file with draw-class
// TODO Is called from Panel::Rechenspiele, should be DataBrowser
?>
<h1>Monatskilometer</h1>

<div class="bigImg" style="width:800px;height:450px;">
	<img id="monatskilometer" src="lib/draw/monatskilometer.php?jahr=<?php echo date("Y"); ?>" />
</div>

	<br />
	<br />

<center>
<?php
for ($j = START_YEAR; $j <= date("Y"); $j++)
	echo NL.Ajax::imgChange('<a href="lib/draw/monatskilometer.php?jahr='.$j.'" style="margin-right:20px;">'.$j.'</a>','monatskilometer');
?>
</center>
<?php
$Frontend->displayFooter();
$Frontend->close();
?>