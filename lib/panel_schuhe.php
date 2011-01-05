	<div id="schuhe">
<?php
$inuse = true;
$db = mysql_query('SELECT `name`, `km`, `inuse` FROM `ltb_schuhe` ORDER BY `inuse` DESC, `km` DESC');
while($schuh = mysql_fetch_array($db)) {
	if ($inuse && $schuh['inuse'] == 0) {
		echo('	<div id="hiddenschuhe" style="display:none;">'.NL);
		$inuse = false;
	}
	echo('		<p style="background-image:url(lib/draw/schuhbalken.php?km='.round($schuh['km']).');"><span>'.km($schuh['km']).'</span> <strong>'.$schuh['name'].'</strong></p>'.NL);	
}
echo('	</div>');
?>
	</div>
	<a class="toggle right" rel="hiddenschuhe" href="#schuhe" name="schuhe">Alte Schuhe anzeigen</a>
	<br class="clear" />