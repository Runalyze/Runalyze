<?php header('Content-type: text/html; charset=ISO-8859-1'); ?>
	<img id="close" src="img/cross.png" onClick="ajax_close()" />
<?php
include_once('../config/functions.php');
connect();

$db = mysql_query('SELECT * FROM `ltb_training` WHERE `id`='.$_GET['id'].' LIMIT 1');
$dat = mysql_fetch_assoc($db);
	
$kleidung = '';
if ($dat['kleidung'] != '') {
	$kleidung_db = mysql_query('SELECT * FROM `ltb_kleidung` WHERE `id` IN ('.$dat['kleidung'].') ORDER BY `order` ASC');
	while ($kleidung_dat = mysql_fetch_array($kleidung_db)) {
		if ($kleidung != '') $kleidung .= ', ';
		$kleidung .= $kleidung_dat['name'];
	}
}

$datum = date("H:i", $dat['time']) != "00:00" ? date("d.m.Y, H:i", $dat['time']).' Uhr' : date("d.m.Y", $dat['time']);
$head = $dat['sportid'] == 1 ? typ($dat['typid']) : sport($dat['sportid']);

$lat = explode('|',$dat['arr_lat']);
$lon = explode('|',$dat['arr_lon']);
?>
<h1><img class="link" onClick="seite('form_training','<?php echo($dat['id']); ?>')" src="img/edit.gif" /> <?php echo($head); ?></h1>

<a class="ajax" href="lib/training_data.php?id=<?php echo($_GET['id']); ?>" target="trainingData">
	<img src="img/graph_data.png" alt="Allgemeine Statistiken" />
</a>
<?php if ($dat['arr_pace'] != ''): ?>
<a class="ajax" href="lib/training_puls.php?id=<?php echo($_GET['id']); ?>" target="trainingData">
	<img src="img/graph_puls.png" alt="Herzfrequenz" />
</a>
<?php endif; ?>
<?php if ($dat['arr_pace'] != ''): ?>
<a class="ajax" href="lib/training_pace.php?id=<?php echo($_GET['id']); ?>" target="trainingData">
	<img src="img/graph_pace.png" alt="Pace und Zwischenzeiten" />
</a>
<?php endif; ?>
<?php if ($dat['arr_alt'] != ''): ?>
<a class="ajax" href="lib/training_alt.php?id=<?php echo($_GET['id']); ?>" target="trainingData">
	<img src="img/graph_alt.png" alt="Höhenprofil" />
</a>
<?php endif; ?>
<?php if (sizeof($lat) > 1 && sizeof($lon) > 1): ?>
<a class="ajax" href="lib/training_iframe.php?id=<?php echo($_GET['id']); ?>" target="trainingData">
	<img src="img/graph_map.png" alt="Streckenansicht" />
</a>
<?php endif; ?>

<div id="trainingData">
<?php include('training_data.php'); ?>
</div>

<?php
close();
?>