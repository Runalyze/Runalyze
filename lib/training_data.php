<?php
header('Content-type: text/html; charset=ISO-8859-1');
include_once('../config/functions.php');
connect();

$db = mysql_query('SELECT * FROM `ltb_training` WHERE `id`='.$_GET['id'].' LIMIT 1');
$dat = mysql_fetch_assoc($db);
?>		
<table style="width:480px;">
	<tr><td class="b">Datum:</td><td colspan="4"><?php echo(wochentag(date("w",$dat['time'])).', '.$datum); ?><br />&nbsp;</td></tr>
<?php if($dat['distanz'] != 0): ?>
	<tr><td class="b">Distanz:</td><td><?php echo(km($dat['distanz'])); ?></td><td />
		<td class="b">Tempo:</td><td><?php echo($dat['pace']); ?>/km --- <?php echo(kmh($dat['distanz'],$dat['dauer'])); ?> km/h</td></tr>
<?php endif; ?>
	<tr><td class="b">Zeit:</td><td><?php echo(dauer($dat['dauer'])); ?></td><td />
		<td class="b">Kalorien:</td><td><?php echo(unbekannt($dat['kalorien'])); ?> kcal</td></tr>
<?php if($dat['puls'] != 0): ?>
	<tr><td class="b">Puls:</td><td colspan="4">&Oslash; <?php echo(unbekannt($dat['puls'])); ?>bpm --- max <?php echo(unbekannt($dat['puls_max'])); ?>bpm</td></tr>
<?php endif; ?>
<?php if($dat['wetter'] != '' OR $dat['temperatur'] != 0 OR $dat['strecke'] != '' OR $kleidung != ''): ?>
	<tr><td colspan="5"><br />&nbsp;</td></tr>
<?php endif; ?>
<?php if($dat['wetter'] != '' OR $dat['temperatur'] != 0): ?>
	<tr><td class="b">Wetter:</td><td colspan="4"><?php echo(wetter($dat['wetterid']).' '.$global['wetter'][$dat['wetterid']]['name'].' bei '.unbekannt($dat['temperatur']).' &#176;C'); ?></td><td />
<?php endif; ?>
<?php if($dat['strecke'] != ''): ?>
	<tr><td class="b">Strecke:</td><td colspan="4"><?php echo($dat['strecke']).($dat['hm'] != 0 ? ' <small>('.$dat['hm'].' H&ouml;henmeter)</small>' : ''); ?></td></tr>
<?php endif; ?>
<?php if($dat['schuhid'] != 0): ?>
	<tr><td class="b">Schuh:</td><td colspan="4"><?php echo(schuh($dat['schuhid'])); ?></td></tr>
<?php endif; ?>
<?php if($kleidung != ''): ?>
	<tr><td class="b">Kleidung:</td><td colspan="4"><?php echo($kleidung); ?></td></tr>
<?php endif; ?>
<?php if($dat['trainingspartner'] != ''): ?>
	<tr><td class="b">Trainingspartner:</td><td colspan="4"><?php echo($dat['trainingspartner']); ?></td></tr>
<?php endif; ?>
</table>
<br />
<strong>Bemerkung:</strong><br />
<?php
if ($dat['laufabc'] == 1) echo('<img src="img/abc.png" alt="Lauf-ABC" /> ');
if ($dat['bemerkung'] != '') echo($dat['bemerkung']);
	else echo('<em>keine</em>');
?>