<div id="sportler">
<div id="sportler-gewicht" class="change">
<?php
$db = mysql_query('SELECT * FROM `ltb_user` ORDER BY `time` DESC LIMIT 1');
$daten = mysql_fetch_assoc($db);
if ($config['use_gewicht'] == 1)
	$left = '<strong title="'.date("d.m.Y",$daten['time']).'">'.$daten['gewicht'].' kg</strong>';

if ($config['use_ruhepuls'] == 1)
	$right = ''.$daten['puls_ruhe'].' bpm / '.$daten['puls_max'].' bpm';

echo('    <p><span>'.$right.'</span> <a class="change" href="sportler-analyse" target="sportler"><del>Analyse</del> / Allgemein:</a> '.$left.'</p>'.NL);
?>
	<center>
		<img src="lib/draw/gewicht.php" alt="Diagramm" style="width:322px; height:150px;" />
	</center> 
</div>
<div id="sportler-analyse" class="change" style="display:none;">
<?php $left = ''; $right = '';
if ($config['use_koerperfett'] == 1)
	$left = '<small>'.$daten['fett'].' &#37;Fett, '.$daten['wasser'].' &#37;Wasser, '.$daten['muskeln'].' &#37;Muskeln</small>';

if ($config['use_blutdruck'] == 1) 
	$right = '<small>Blutdruck: '.$daten['blutdruck_min'].' zu '.$daten['blutdruck_max'];

echo('    <p><span>'.$right.'</span> <a class="change" href="sportler-gewicht" target="sportler">Analyse / <del>Allgemein</del>:</a> '.$left.'</p>'.NL);
?>
	<center>
		<img src="lib/draw/fett.php" alt="Diagramm" style="width:322px; height:150px;" />
	</center> 
</div>
</div>