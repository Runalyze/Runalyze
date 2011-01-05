<?php
if (!$include_sports) {
	header('Content-type: text/html; charset=ISO-8859-1');
	include_once('../../config/functions.php');
	connect();
}

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

$lat = explode('|',$dat['arr_lat']);
$lon = explode('|',$dat['arr_lon']);
?>
<h1>
	<img class="link" onClick="seite('form_training','<?php echo($dat['id']); ?>')" src="img/edit.png" alt="Training editieren" />
<?php
echo ($dat['sportid'] == 1) ? typ($dat['typid']) : sport($dat['sportid']);
if ($dat['laufabc'] == 1)
	echo(' <img src="img/abc.png" alt="Lauf-ABC" />');
if ($dat['bemerkung'] != '')
	echo (": ".$dat['bemerkung']);
?>
	<small class="right">
		<?php echo(wochentag(date("w",$dat['time'])).', '.$datum); ?>
	</small>
</h1>

<small class="right">
<?php
$src = '';
if ($dat['arr_pace'] != '') {
	$src = 'training_pace';
	echo('
	<a class="jImg" rel="trainingGraph" href="lib/draw/training_pace.php?id='.$_GET['id'].'">
		Pace
	</a>');
}
if ($dat['splits'] != '') {
	if ($src != '')
		echo('|');
	else
		$src = 'splits';
	echo('
	<a class="jImg" rel="trainingGraph" href="lib/draw/splits.php?id='.$_GET['id'].'">
		Splits
	</a>');
}
if ($dat['arr_heart'] != '') {
	if ($src != '')
		echo('|');
	else
		$src = 'training_puls';
	echo('
	<a class="jImg" rel="trainingGraph" href="lib/draw/training_puls.php?id='.$_GET['id'].'">
		Puls
	</a>');
}
if ($dat['arr_alt'] != '') {
	if ($src != '')
		echo('|');
	else
		$src = 'training_alt';
	echo('
	<a class="jImg" rel="trainingGraph" href="lib/draw/training_hm.php?id='.$_GET['id'].'">
		H&ouml;henprofil
	</a>');
}
?>
</small>

<br class="clear" />

<?php
if ($src != '')
	echo('
<img id="trainingGraph" class="right" src="lib/draw/'.$src.'.php?id='.$_GET['id'].'" style="margin:10px 0;" />');
?>

<br class="right" />

<?php if (sizeof($lat) > 1 && sizeof($lon) > 1): ?>
<iframe class="right" src="http://localhost/ltb/lib/gpx/karte.php?id=<?php echo $_GET['id']; ?>" style="border:0; width:482px; height:300px;" frameborder="0"></iframe>
<?php endif; ?>

<table>
<?php if($dat['distanz'] != 0): ?>
	<tr>
		<td class="b">Distanz:</td>
		<td><?php echo(km($dat['distanz'])); ?></td>
	</tr>
<?php endif; ?>
	<tr>
		<td class="b">Zeit:</td>
		<td><?php echo(dauer($dat['dauer'])); ?></td>
	</tr>
<?php if($dat['distanz'] != 0): ?>
	<tr>
		<td class="b">Tempo:</td>
		<td><?php echo($dat['pace']); ?>/km<br />
			<?php echo(kmh($dat['distanz'],$dat['dauer'])); ?> km/h</td>
	</tr>
<?php endif; ?>
	<tr>
		<td class="b">Kalorien:</td>
		<td><?php echo(unbekannt($dat['kalorien'])); ?> kcal</td>
	</tr>
<?php if($dat['puls'] != 0): ?>
	<tr>
		<td class="b">Puls:</td>
		<td>&Oslash; <?php echo(unbekannt($dat['puls'])); ?>bpm<br />
			max. <?php echo(unbekannt($dat['puls_max'])); ?>bpm</td>
	</tr>
<?php endif; ?>
<?php if($dat['wetter'] != '' OR $dat['temperatur'] != 0 OR $dat['strecke'] != '' OR $kleidung != ''): ?>
	<tr><td colspan="5"><br />&nbsp;</td></tr>
<?php endif; ?>
<?php if($dat['wetter'] != '' OR $dat['temperatur'] != 0): ?>
	<tr>
		<td class="b">Wetter:</td>
		<td><?php echo(wetter($dat['wetterid']).' '.$global['wetter'][$dat['wetterid']]['name'].' bei '.unbekannt($dat['temperatur']).' &#176;C'); ?></td>
	</tr>
<?php endif; ?>
<?php if($dat['strecke'] != ''): ?>
	<tr>
		<td class="b">Strecke:</td>
		<td><?php echo($dat['strecke']).($dat['hm'] != 0 ? ' <small>('.$dat['hm'].' H&ouml;henmeter)</small>' : ''); ?></td>
	</tr>
<?php endif; ?>
<?php if($dat['schuhid'] != 0): ?>
	<tr>
		<td class="b">Schuh:</td>
		<td><?php echo(schuh($dat['schuhid'])); ?></td>
	</tr>
<?php endif; ?>
<?php if($kleidung != ''): ?>
	<tr>
		<td class="b">Kleidung:</td>
		<td><?php echo($kleidung); ?></td>
	</tr>
<?php endif; ?>
<?php if($dat['trainingspartner'] != ''): ?>
	<tr>
		<td class="b">Trainingspartner:</td>
		<td><?php echo($dat['trainingspartner']); ?></td>
	</tr>
<?php endif; ?>
</table>

<?php
$arr['time'] = explode('|', $dat['arr_time']);
$arr['heart'] = explode('|', $dat['arr_heart']);
$arr['dist'] = explode('|', $dat['arr_dist']);
$arr['alt'] = explode('|', $dat['arr_alt']);
if (sizeof($arr['dist']) > 1 && sizeof($arr['time']) > 1):
?>
<strong>Berechnete Rundenzeiten:</strong><br />
<table cellspacing="0">
<?php
	$km = 1;
	$kmi = array(0);
	$hm_p = 0; $hm_m = 0;
	$arr_len = sizeof($arr['dist']);
	foreach ($arr['dist'] as $i => $dist) {
		if (floor($dist) == $km || $i == $arr_len-1) {
			$km++;
			$kmi[] = $i;
			echo('
	<tr class="a'.($i%2+1).' r">
		<td>'.dauer($arr['time'][$i]).'</td>
		<td>'.km($dist,2).'</td>
		<td class="small">'.pace(($arr['dist'][$i]-$arr['dist'][$kmi[sizeof($kmi)-2]]),($arr['time'][$i]-$arr['time'][$kmi[sizeof($kmi)-2]])).'</td>');
			if (sizeof($arr['heart']) > 1) {
				$this_heart = array_slice($arr['heart'],$kmi[sizeof($kmi)-2],($i-$kmi[sizeof($kmi)-2]));
				echo('
		<td class="small">'.round(array_sum($this_heart)/count($this_heart)).'</td>');
			}
			if (sizeof($arr['alt']) > 1)
				echo('
		<td class="small">+'.$hm_p.'/-'.$hm_m.'</td>');
			echo('
	</tr>');
			$hm_p = 0; $hm_m = 0;
		} elseif ($i != 0 && sizeof($arr['alt']) > 1 && $arr['alt'][$i] != 0 && $arr['alt'][$i-1] != 0) {
			$hm_diff = $arr['alt'][$i] - $arr['alt'][$i-1];
			$hm_p += ($hm_diff > 0) ? $hm_diff : 0;
			$hm_m -= ($hm_diff < 0) ? $hm_diff : 0;
		}
	}
?>
</table>
<?php endif; ?>


<?php
// Splits
if ($dat['splits'] != ''):
$dat['splits'] = str_replace("\r\n", "-", $dat['splits']);
$splits = explode("-", $dat['splits']);
?>
<strong>Eigene Rundenzeiten:</strong><br />
<table cellspacing="0">
<?php
for ($i = 0; $i < count($splits); $i++) {
	$split = explode("|", $splits[$i]);
	$zeit_dat = explode(":", $split[1]);
	$distanz[] = $split[0];
	$zeit[] = round(($zeit_dat[0]*60 + $zeit_dat[1])/$split[0]);
		
	echo('
	<tr class="a'.($i%2+1).'">
		<td class="b">'.km($split[0]).'</td>
		<td>'.dauer($zeit_dat[0]*60 + $zeit_dat[1]).'</td>
		<td class="small">'.pace(1,$zeit[$i]).'/km</td>
	</tr>');
}
?>
</table>
<?php endif; ?>

<br class="clear" />
<?php
close();
?>