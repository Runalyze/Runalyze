<?php header('Content-type: text/html; charset=ISO-8859-1');
include_once('../../config/functions.php');
connect();
?>
<h1>Wettk&auml;mpfe</h1>
	<small class="right">
		<a class="change" href="#alle" target="tab_content" >Alle Wettk&auml;mpfe</a> |
		<a class="change" href="#last_wks" target="tab_content">Letzten Wettk&auml;mpfe</a> |
		<a class="change" href="#bestzeiten" target="tab_content" name="bestzeit-dia">Bestzeiten</a>
	</small>
	<br />
 
<div id="alle" class="change" style="display:none;">
<table cellspacing="0" width="100%">
	<tr class="b c">
		<td>Datum</td>
		<td>Lauf</td>
		<td>Distanz</td>
		<td>Zeit</td>
		<td>Pace</td><?php
if ($config['use_puls']) echo('
		<td>Puls</td>');
if ($config['use_wetter']) echo('
		<td>Wetter</td>');
?>	</tr>  
	<tr class="space">
		<td colspan="7" />
	</tr>
<?php
function show_wk_table($wk) {
	global $i, $config;
	$i++;
	echo('
	<tr class="a'.($i%2 + 1).' r">
		<td class="c small"><a href="#" onclick="daten(\''.$wk['time'].'\',\''.wochenstart($wk['time']).'\',\''.wochenende($wk['time']).'\')">'.date("d.m.Y", $wk['time']).'</a></td>
		<td class="l"><strong class="link" onClick="seite(\'training\',\''.$wk['id'].'\')">'.$wk['bemerkung'].'</strong></td>
		<td>'.km($wk['distanz'], (round($wk['distanz']) != $wk['distanz'] ? 1 : 0), $wk['bahn']).'</td>
		<td>'.zeit($wk['dauer']).'</td>
		<td class="small">'.$wk['pace'].'/km</td>');
	if ($config['use_puls'])
		echo('
		<td class="small">'.unbekannt($wk['puls']).' / '.unbekannt($wk['puls_max']).' bpm</td>');
	if ($config['use_wetter'])
		echo('
		<td class="small">'.($wk['temperatur'] != 0 && $wk['wetterid'] != 0 ? $wk['temperatur'].' &deg;C '.wetter($wk['wetterid']) : '').'</td>');
	echo('
	</tr>');	
}

$i = 0;
$wk_db = mysql_query('SELECT * FROM `ltb_training` WHERE `typid`='.$global['wettkampf_typid'].' ORDER BY `time` ASC');
while ($wk = mysql_fetch_array($wk_db))	show_wk_table($wk); 
?>
	<tr class="space">
		<td colspan="7" />
	</tr>
</table>
</div>

<div id="last_wks" class="change" style="display:block;">
<table cellspacing="0" width="100%">
	<tr class="b c">
		<td>Datum</td>
		<td>Lauf</td>
		<td>Distanz</td>
		<td>Zeit</td>
		<td>Pace</td><?php
if ($config['use_puls']) echo('
		<td>Puls</td>');
if ($config['use_wetter']) echo('
		<td>Wetter</td>');
?>	</tr>  
	<tr class="space">
		<td colspan="7" />
	</tr>
<?php
$i = 0;
$wk_db = mysql_query('SELECT * FROM `ltb_training` WHERE `typid`='.$global['wettkampf_typid'].' ORDER BY `time` DESC LIMIT 5');
while ($wk = mysql_fetch_array($wk_db))
	show_wk_table($wk); 
?>
 	<tr class="space">
 		<td colspan="7" />
 	</tr>
</table>
</div>
 
<?php # BESTZEITEN ?>
<div id="bestzeiten" class="change" style="display:none;">
<table cellspacing="0" width="100%">
	<tr class="b c">
		<td>Datum</td>
		<td>Lauf</td>
		<td>Distanz</td>
		<td>Zeit</td>
		<td>Pace</td><?php
if ($config['use_puls']) echo('
		<td>Puls</td>');
if ($config['use_wetter']) echo('
		<td>Wetter</td>');
?>	</tr>  
	<tr class="space">
		<td colspan="7" />
	</tr>
<?php
$distanzen = array();
$dist_db = mysql_query('SELECT `distanz`, SUM(1) as `wks` FROM `ltb_training` WHERE `typid`='.$global['wettkampf_typid'].' GROUP BY `distanz`');
while ($dist = mysql_fetch_assoc($dist_db)) {
	$i = 0;
	if ($dist['wks'] > 1)
		$distanzen[] = $dist['distanz'];
	$wk_db = mysql_query('SELECT * FROM `ltb_training` WHERE `typid`='.$global['wettkampf_typid'].' AND `distanz`='.$dist['distanz'].' ORDER BY `dauer` ASC LIMIT 1');
	while ($wk = mysql_fetch_array($wk_db))
		show_wk_table($wk);
}
?>
	<tr class="space">
		<td colspan="7" />
	</tr>
</table>

	<small style="text-align:center;display:block;">
<?php
$first = true;
foreach ($distanzen as $km) {
	echo('
		'.(!$first ? '|' : '').' <a href="#bestzeit-dia" onclick="document.getElementById(\'bestzeit-diagramm\').src=\'lib/draw/bestzeit.php?km='.$km.'\';">'.km($km, (round($km) != $km ? 1 : 0)).'</a>');
	$first = false;
}
?>
	</small>

	<center>
		<img id="bestzeit-diagramm" src="lib/draw/bestzeit.php?km=10" width="482" height="192" />
	</center>
</div>


<table cellspacing="0" width="100%">
	<tr class="b c">
		<td></td>
<?php
$jahr = array();
$jahr_db = mysql_query('SELECT YEAR(FROM_UNIXTIME(`time`)) as `jahr`, `distanz`, `dauer` FROM `ltb_training` WHERE `typid`='.$global['wettkampf_typid'].' ORDER BY `jahr` ASC');
while ($dat = mysql_fetch_assoc($jahr_db)) {
	if (!isset($jahr[$dat['jahr']]))
		$jahr[$dat['jahr']] = array('sum' => 0, 'sum_5' => 0, 'pb_5' => 360000, 'sum_10' => 0, 'pb_10' => 360000, 'sum_hm' => 0, 'pb_hm' => 360000, 'sum_m' => 0, 'pb_m' => 360000);
	$jahr[$dat['jahr']]['sum']++;
	switch($dat['distanz']) {
		case 5: $jahr[$dat['jahr']]['sum_5']++; if ($dat['dauer'] < $jahr[$dat['jahr']]['pb_5']) $jahr[$dat['jahr']]['pb_5'] = $dat['dauer']; break;
		case 10: $jahr[$dat['jahr']]['sum_10']++; if ($dat['dauer'] < $jahr[$dat['jahr']]['pb_10']) $jahr[$dat['jahr']]['pb_10'] = $dat['dauer']; break;
		case 21.1: $jahr[$dat['jahr']]['sum_hm']++; if ($dat['dauer'] < $jahr[$dat['jahr']]['pb_hm']) $jahr[$dat['jahr']]['pb_hm'] = $dat['dauer']; break;
		case 42.2: $jahr[$dat['jahr']]['sum_m']++; if ($dat['dauer'] < $jahr[$dat['jahr']]['pb_m']) $jahr[$dat['jahr']]['pb_m'] = $dat['dauer']; break;
	}
}
		
foreach ($jahr as $j => $j_dat)
	echo('
		<td>'.$j.'</td>');
?>
	</tr>
	<tr class="space">
		<td colspan="<?php echo (sizeof($jahr)+1); ?>" />
	</tr>
	<tr class="a1 r">
		<td class="b l">Gesamt</td>
<?php
foreach ($jahr as $j => $j_dat)
	echo('
		<td>'.$j_dat['sum'].'x</td>');
?>
	</tr>
	<tr class="a2 r">
		<td class="b l">5 km</td>
<?php
foreach ($jahr as $j => $j_dat)
	echo('
		<td>'.($j_dat['sum_5'] != 0 ? '<small>'.dauer($j_dat['pb_5']).'</small> '.$j_dat['sum_5'].'x' : '&nbsp;').'</td>');
?>
	</tr>
	<tr class="a1 r">
		<td class="b l">10 km</td>
<?php
foreach ($jahr as $j => $j_dat)
	echo('
		<td>'.($j_dat['sum_10'] != 0 ? '<small>'.dauer($j_dat['pb_10']).'</small> '.$j_dat['sum_10'].'x' : '&nbsp;').'</td>');
?>
	</tr>
	<tr class="a2 r">
		<td class="b l">HM</td>
<?php
foreach ($jahr as $j => $j_dat)
	echo('
		<td>'.($j_dat['sum_hm'] != 0 ? '<small>'.dauer($j_dat['pb_hm']).'</small> '.$j_dat['sum_hm'].'x' : '&nbsp;').'</td>');
?>
	</tr>
	<tr class="a1 r">
		<td class="b l">M</td>
<?php
foreach ($jahr as $j => $j_dat)
	echo('
		<td>'.($j_dat['sum_m'] != 0 ? '<small>'.dauer($j_dat['pb_m']).'</small> '.$j_dat['sum_m'].'x' : '&nbsp;').'</td>');
?>
	</tr>
	<tr class="space">
		<td colspan="<?php echo (sizeof($jahr)+1); ?>" />
	</tr>
</table>