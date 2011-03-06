<?php
/**
 * File for displaying the kilometers of each week.
 * Call:   inc/plugin/window.wochenkilometer.php
 */
require('../class.Frontend.php');
$Frontend = new Frontend(true, __FILE__);
$Frontend->displayHeader();

// TODO Get new draw-file with draw-class
// TODO Is called from Panel::Rechenspiele, should be DataBrowser
?>
<h1>Wochenkilometer</h1>

<div class="bigImg" style="width:800px;height:450px;">
	<img id="wochenkilometer" src="lib/draw/wochenkilometer.php?jahr=<?php echo date("Y"); ?>" />
</div>

	<br />
	<br />

<center>
<?php
for ($j = START_YEAR; $j <= date("Y"); $j++)
	echo NL.Ajax::imgChange('<a href="lib/draw/wochenkilometer.php?jahr='.$j.'" style="margin-right:20px;">'.$j.'</a>','wochenkilometer');
?>
</center>
<?php
$Frontend->displayFooter();
$Frontend->close();
?>