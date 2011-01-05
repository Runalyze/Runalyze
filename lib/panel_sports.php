	<div id="sports">
<?php $include_sports = true; include('lib/panel_sports_single.php'); ?>
	</div>
	<span id="sports-links" style="display:none;">
		<a class="right" href="#" onClick="refreshSports(0,<?php echo(time()); ?>)">All</a>
<?php
for ($i = 2006; $i <= date("Y"); $i++) {
	$start = mktime(0,0,0,1,1,$i);
	$ende = mktime(23,59,59,1,0,$i+1);
	echo ('
		<a href="#" onClick="refreshSports('.$start.', '.$ende.')">'.$i.'</a>: ');
	$mstart = $i == 2006 ? 8 : 1;
	$mende = $i == date("Y") ? date("m") : 12;
	for ($j = $mstart; $j <= $mende; $j++) {
		$start = mktime(0,0,0,$j,1,$i);
		$ende = mktime(23,59,59,$j+1,0,$i);
		echo ('
		<a href="#" onClick="refreshSports('.$start.', '.$ende.')">'.$j.'</a>');
		if ($j != $mende) echo(', ');
	}
	if ($i != date("Y")) echo('<br />'.NL);
}
?>
<br />
	</span>
	<div style="clear:both;"></div>