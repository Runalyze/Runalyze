<?php header('Content-type: text/html; charset=ISO-8859-1'); ?>
<h1>Schuhe</h1>
 
<table cellspacing="0" width="100%">
	<tr class="b c">
		<td colspan="2" />
		<td>Kaufdatum</td>
		<td colspan="2">max.</td>
		<td>&Oslash; km</td>
		<td>&Oslash; Pace</td>
		<td>Dauer</td>
		<td>Distanz</td>
	</tr>
	<tr class="space">
		<td colspan="9" />
	</tr>
<?php
include_once('../../config/functions.php');
connect();

$i = 0;

$schuh_db = mysql_query('SELECT * FROM `ltb_schuhe` ORDER BY `inuse` DESC, `km` DESC');
while ($schuh = mysql_fetch_array($schuh_db)) {
	$i++;
	$training_dist_db = mysql_query('SELECT * FROM `ltb_training` WHERE `schuhid`='.$schuh['id'].' ORDER BY `distanz` DESC LIMIT 1');
	$training_dist = mysql_fetch_assoc($training_dist_db);
	$laengster = '<span class="link" onClick="seite(\'training\',\''.$training_dist['id'].'\')">'.km($training_dist['distanz']).'</span>';
	$training_pace_db = mysql_query('SELECT * FROM `ltb_training` WHERE `schuhid`='.$schuh['id'].' ORDER BY `pace` ASC LIMIT 1');
	$training_pace = mysql_fetch_assoc($training_pace_db);
	$schnellster = '<span class="link" onClick="seite(\'training\',\''.$training_pace['id'].'\')">'.$training_pace['pace'].'/km</span>';
	$training_sum_db = mysql_query('SELECT * FROM `ltb_training` WHERE `schuhid`="'.$schuh['id'].'"');
	$trainings = mysql_num_rows($training_sum_db);
	$in_use = $schuh['inuse']==1 ? '' : ' small';
	$km_avg = ($trainings != 0) ? km($schuh['km']/$trainings) : '-';
	echo('
	<tr class="a'.($i%2 + 1).' r" style="background:url(lib/draw/schuhbalken.php?km='.round($schuh['km']).') no-repeat bottom left;">
		<td class="small">'.$trainings.'x</td>
		<td class="b'.$in_use.' l"><span class="link" onclick="submit_suche(\'dat[0]=schuhid&opt[0]=is&val[0]='.$schuh['id'].'\')">'.$schuh['name'].'</span></td>
		<td class="small">'.$schuh['kaufdatum'].'</td>
		<td>'.$laengster.'</td>
		<td>'.$schnellster.'</td>
		<td>'.(($trainings != 0) ? km($schuh['km']/$trainings) : '-').'</td>
		<td>'.(($trainings != 0) ? pace($schuh['km'], $schuh['dauer']) : '-').'/km</td>
		<td>'.dauer($schuh['dauer']).'</td>
		<td>'.km($schuh['km']).'</td>
	</tr>');
}
echo('
	<tr class="space"><td colspan="9" /></tr>'); 
?>
</table>