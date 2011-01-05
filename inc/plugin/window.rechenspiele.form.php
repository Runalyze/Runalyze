<?php
/**
 * File for displaying statistic plugins.
 * Call:   inc/plugin/window.rechenspiele.form.php
 */
require('../class.Frontend.php');
$Frontend = new Frontend(true, __FILE__);
$Frontend->displayHeader();

// TODO Get new draw-file with draw-class
?>
<h1>Formkurve</h1>

<div class="bigImg" style="width:800px;height:450px;">
	<img id="formkurve" src="lib/draw/mued.php?jahr=<?php echo date("Y"); ?>" />
</div>

	<br />
	<br />

<center>
<?php
for ($j = START_YEAR; $j <= date("Y"); $j++)
	echo NL.Ajax::imgChange('<a href="lib/draw/mued.php?jahr='.$j.'" style="margin-right:20px;">'.$j.'</a>','formkurve');
?>
</center>
<?php
$Frontend->displayFooter();
$Frontend->close();
?>