<?php
header('Content-type: text/html; charset=ISO-8859-1');

if (!$include_sports) {
	include_once('../../config/functions.php');
	connect();
}

if ($_GET['jahr'] == -1) {
	$i = 0;
	$jahr = "Gesamt";
	$jstart = mktime(0,0,0,1,1,$config['startjahr']);
	$jende = time();	
}

else {
	$i = ($_GET['jahr']=='undefined' || !isset($_GET['jahr'])) ? date("Y") : $_GET['jahr'];
	$jahr = $i;
	$jstart = mktime(0,0,0,1,1,$i);
	$jende = mktime(23,59,59,1,0,$i+1);
}

$sid = $global['hauptsport'];
?>
<h1>
	<?php echo (check_modus('wetterid') != 0) ?
	'<img class="link right" title="Diagramme" onClick="diagramm(\'wetter\',\''.date("Y").'\')" src="img/wk.png" />
	Wetter' :
	'Kleidung'; ?>
</h1>

<small class="left">
	<?php echo sport($sid); ?>
</small>

<small class="right">
<?php for ($x = $config['startjahr']; $x <= date("Y"); $x++): ?>
	<a class="ajax" href="lib/stats/wetter.php?jahr=<?php echo($x); ?>" target="tab_content"><?php echo($x); ?></a> |
<?php endfor; ?>
	<a class="ajax" href="lib/stats/wetter.php?jahr=-1" target="tab_content">Gesamt</a>
</small>

	<br class="clear" />

<table cellspacing="0" width="100%" class="small">
	<tr class="b c">
		<td><?php echo($jahr); ?></td>
		<td width="8%">Jan</td>
		<td width="8%">Feb</td>
		<td width="8%">Mrz</td>
		<td width="8%">Apr</td>
		<td width="8%">Mai</td>
		<td width="8%">Jun</td>
		<td width="8%">Jul</td>
		<td width="8%">Aug</td>
		<td width="8%">Sep</td>
		<td width="8%">Okt</td>
		<td width="8%">Nov</td>
		<td width="8%">Dez</td>
	</tr>
	<tr class="space">
		<td colspan="13" />
	</tr>
	<tr class="a2 r">
		<td class="c">&#176;C</td>
<?php // Temperatur
if ($i == 0) { # Gesamt
	$temp_avg = array();
	$num = array();
	$temp_db = mysql_query('SELECT `temperatur`, `time` FROM `ltb_training` WHERE `sportid`="'.$sid.'" AND `temperatur`!=0');
	while ($temp = mysql_fetch_assoc($temp_db)) {
		$temp_avg[date("n",$temp['time']+20)] += $temp['temperatur'];
		$num[date("n",$temp['time']+20)] ++;
	}
	for ($m = 1; $m <= 12; $m++):
?>
		<td><?php echo ($num[$m] == 0) ? '&nbsp;' : round(($temp_avg[$m]/$num[$m]),0).' &deg;C'; ?></td>
<?php
	endfor;
}
else { # Jahr
	for ($m = 1; $m <= 12; $m++) {	
		$start = mktime(0,0,0,$m,1,$i);
		$ende = mktime(23,59,59,$m+1,0,$i);
		$num_temp = mysql_query('SELECT `id` FROM `ltb_training` WHERE `sportid`="'.$sid.'" AND `temperatur`!=0 AND `time` BETWEEN '.$start.' AND '.$ende.' LIMIT 1');
		if (mysql_num_rows($num_temp) != 0):
			$temp_db = mysql_query('SELECT AVG(`temperatur`) as `temp_avg` FROM `ltb_training` WHERE `sportid`="'.$sid.'" AND `temperatur`!=0 AND `time` BETWEEN '.$start.' AND '.$ende.' LIMIT 1');
			while ($temp = mysql_fetch_assoc($temp_db)):
?>
		<td><?php echo(round($temp['temp_avg'],0).' &deg;C'); ?></td>
<?php
			endwhile;
		else:
?>
		<td>&nbsp;</td>
<?php
		endif;
	}
}
?>
	</tr>
<?php // Wetterarten
$x = 1;
$wetter_db = mysql_query('SELECT * FROM `ltb_wetter` WHERE `name`!="unbekannt" ORDER BY `order` ASC');
while ($wetter = mysql_fetch_assoc($wetter_db)): $x++;
?>
	<tr class="a<?php echo ($x%2+1);?> r">
		<td class="c"><?php echo wetter($wetter['id']); ?></td>
<?php
	if ($i == 0) { #Gesamt
		$num = array();
		$num_db = mysql_query('SELECT `wetterid`, `time`, `id` FROM `ltb_training` WHERE `sportid`="'.$sid.'" AND `wetterid`="'.$wetter['id'].'"');
		while ($num_dat = mysql_fetch_assoc($num_db))
			$num[date("n",$num_dat['time']+20)] ++;
		for ($j = 1; $j <= 12; $j++):
?>
		<td><?php echo ($num[$j] != 0) ? ($num[$j].'x') : '&nbsp;'; ?></td>
<?php
		endfor;
	}
	else { # Jahr
		for ($j = 1; $j <= 12; $j++) {	
			$start = mktime(0,0,0,$j,1,$i);
			$ende = mktime(23,59,59,$j+1,0,$i);
			$num_db = mysql_query('SELECT `wetterid` FROM `ltb_training` WHERE `sportid`="'.$sid.'" AND `wetterid`="'.$wetter['id'].'" AND `time` BETWEEN '.$start.' AND "'.$ende.'"');
			$anzahl = mysql_num_rows($num_db);
?>
		<td><?php echo ($anzahl != 0) ? ($anzahl.'x') : '&nbsp;'; ?></td>
<?php
		}
	}
?>
	</tr>
<?php endwhile; ?>
	<tr class="space">
		<td colspan="13" />
	</tr>
<?php // Kleidungsarten
$x = 1;
$kleidung_db = mysql_query('SELECT * FROM `ltb_kleidung` ORDER BY `order` ASC');
while ($kleidung = mysql_fetch_assoc($kleidung_db)): $x++;
?>
	<tr class="a<?php echo ($x%2+1); ?> r">
		<td><?php echo $kleidung['name']; ?></td>
<?php
	if ($i == 0) { # Gesamt
		$num = array();
		$num_db = mysql_query('SELECT `kleidung`, `time`, `id`, FIND_IN_SET("'.$kleidung['id'].'", `kleidung`) as `find` FROM `ltb_training` WHERE `sportid`="'.$sid.'" HAVING `find` != "0"');
		while ($num_dat = mysql_fetch_assoc($num_db)) {
			$num[date("n",$num_dat['time']+20)] ++;
		}
		$num_fill = array();
		$num_fill_db = mysql_query('SELECT `kleidung`, `time` FROM `ltb_training` WHERE `sportid`="'.$sid.'" AND `kleidung` != ""');
		while ($num_fill_dat = mysql_fetch_assoc($num_fill_db)) {
			$num_fill[date("n",$num_fill_dat['time']+20)] ++;
		}
		for ($j = 1; $j <= 12; $j++):
?>
		<td><span title="<?php echo($num[$j]); ?>x"><?php echo ($num[$j] != 0) ? (round($num[$j]*100/$num_fill[$j]).' &#37;') : '&nbsp;'; ?></span></td>
<?php
		endfor;
	}
	else { # Jahr
		for ($j = 1; $j <= 12; $j++) {	
			$start = mktime(0,0,0,$j,1,$i);
			$ende = mktime(23,59,59,$j+1,0,$i);
			$num_db = mysql_query('SELECT `kleidung`, `time`, `id`, FIND_IN_SET("'.$kleidung['id'].'", `kleidung`) as `find` FROM `ltb_training` WHERE `sportid`="'.$sid.'" AND `time` BETWEEN '.$start.' AND '.$ende.' HAVING `find` != "0"');
			$num_fill_db = mysql_query('SELECT `kleidung` FROM `ltb_training` WHERE `sportid`="'.$sid.'" AND `time` BETWEEN '.$start.' AND '.$ende.' HAVING `kleidung` != ""');
			if (mysql_num_rows($num_db) == 0)
				echo ('<td>&nbsp;</td>');
			else
				echo ('<td class="r"><span title="'.mysql_num_rows($num_db).' x">'.round(mysql_num_rows($num_db)*100/mysql_num_rows($num_fill_db)).' &#37;</span></td>');
		}
	}
?>
	</tr>
<?php endwhile; ?>
	<tr class="space">
		<td colspan="13" />
	</tr>
	<tr>
		<td colspan="13">&nbsp;</td>
	</tr>
</table>
 
<table cellspacing="0" width="100%" class="small">
	<tr class="b c">
		<td />
		<td>Temperaturen</td>
		<td>&Oslash;</td>
		<td colspan="2" />
		<td>Temperaturen</td>
		<td>&Oslash;</td>
		<td colspan="2" />
		<td>Temperaturen</td>
		<td>&Oslash;</td>
	</tr>
	<tr class="space">
		<td colspan="11" />
	</tr>
	<tr class="a1 r">
<?php // Temperaturbereiche
$i = 0;
$kleidung_db = mysql_query('SELECT * FROM `ltb_kleidung` ORDER BY `order` ASC');
while ($kleidung = mysql_fetch_assoc($kleidung_db)) {
	$i++;
	if ($i%3 == 1):
?>
	</tr>
	<tr class="a<?php echo ($i%2+1);?> r">
<?php else: ?>
		<td>&nbsp;&nbsp;</td>
<?php
	endif;
	$training_db = mysql_query('SELECT AVG(`temperatur`) as `avg`, MAX(`temperatur`) as `max`, MIN(`temperatur`) as `min` FROM `ltb_training` WHERE `sportid`="'.$sid.'" AND `time` BETWEEN '.$jstart.' AND '.$jende.' AND `temperatur`!="0" AND FIND_IN_SET('.$kleidung['id'].',`kleidung`) != 0');
	$dat = mysql_fetch_assoc($training_db);
?>
		<td class="l"><?php echo($kleidung['name']); ?></td>
<?php if ($dat['min'] != ''): ?>
		<td><?php echo($dat['min']); ?>&deg;C bis <?php echo($dat['max']); ?>&deg;C</td>
		<td><?php echo round($dat['avg']); ?>&deg;C</td>
<?php else: ?>
		<td colspan="2" class="c"><em>-</em></td>
<?php endif;
}
for ($i = $i; $i%3 != 1; $i++):
?>
		<td colspan="3">&nbsp;</td>
<?php endfor; ?>
	</tr>
	<tr class="space">
		<td colspan="11" />
	</tr>
</table>