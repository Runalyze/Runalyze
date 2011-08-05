<?php
/**
 * File for displaying the kilometers of each week.
 * Call:   call/window.wochenkilometer.php
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend(true, __FILE__);
$Frontend->displayHeader();
?>

<h1>Wochenkilometer</h1>

<div class="bigImg" style="width:800px;height:500px;">
	<img id="wochenkilometer" src="inc/draw/kilometer.week.php?y=<?php echo date("Y"); ?>" />
</div>

	<br />
	<br />

<center>
	<?php
	for ($j = START_YEAR; $j <= date("Y"); $j++)
		echo NL.Ajax::imgChange('<a href="inc/draw/kilometer.week.php?y='.$j.'" style="margin-right:20px;">'.$j.'</a>','wochenkilometer');
	?>
</center>

<?php
$Frontend->displayFooter();
$Frontend->close();
?>