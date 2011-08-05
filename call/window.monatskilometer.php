<?php
/**
 * File for displaying the kilometers of each month.
 * Call:   call/window.monatskilometer.php
 */
require '../inc/class.Frontend.php';
$Frontend = new Frontend(true, __FILE__);
$Frontend->displayHeader();
?>

<h1>Monatskilometer</h1>

<div class="bigImg" style="width:800px;height:500px;">
	<img id="monatskilometer" src="inc/draw/kilometer.month.php?y=<?php echo date("Y"); ?>" />
</div>

	<br />
	<br />

<center>
	<?php
	for ($j = START_YEAR; $j <= date("Y"); $j++)
		echo NL.Ajax::imgChange('<a href="inc/draw/kilometer.month.php?y='.$j.'" style="margin-right:20px;">'.$j.'</a>','monatskilometer');
	?>
</center>

<?php
$Frontend->displayFooter();
$Frontend->close();
?>