<h1>Rekorde</h1>

<?php
$rekorde = array();
$rekorde[] = array('name' => 'Schnellsten Trainings',
	'sportquery' => 'SELECT * FROM `ltb_sports` WHERE `distanztyp`=1 ORDER BY `id` ASC',
	'datquery' => 'SELECT `id`, `time`, `dauer`, `distanz`, `sportid` FROM `ltb_training` WHERE `sportid`=\'.$sport[\'id\'].\' ORDER BY `pace` ASC, `dauer` DESC LIMIT 10',
	'eval' => '0');
$rekorde[] = array('name' => 'L&auml;ngsten Trainings',
	'sportquery' => 'SELECT * FROM `ltb_sports` ORDER BY `id` ASC',
	'datquery' => 'SELECT * FROM `ltb_training` WHERE `sportid`=\'.$sport[\'id\'].\' ORDER BY `distanz` DESC, `dauer` DESC LIMIT 10',
	'eval' => '1');

foreach ($rekorde as $rekord):
?>
<table cellspacing="0" width="100%" class="small">
	<tr class="b">
		<td colspan="11">
			<?php echo $rekord['name']; ?>
		</td>
	</tr>
	<tr class="space">
		<td colspan="11">
		</td>
	</tr>
<?php
$i = 0;
eval('$sport_db =mysql_query(\''.$rekord['sportquery'].'\');');
while ($sport = mysql_fetch_array($sport_db)) {
	$i++;
	echo('
	<tr class="a'.($i%2 + 1).' r">
		<td class="b l">
			<img src="img/sports/'.$sport['bild'].'" /> '.$sport['name'].'
		</td>');
	eval('$dat_db = mysql_query(\''.$rekord['datquery'].'\');');
	while($dat = mysql_fetch_array($dat_db)) { $i++;
		if ($rekord['eval'] == 0)
			$code = tempo($dat['distanz'],$dat['dauer'],$sport['id'],false);
		elseif ($rekord['eval'] == 1)
			$code = ($dat['distanz'] != 0 ? km($dat['distanz']) : zeit($dat['dauer']));
		echo('
		<td>
			<span class="link" title="'.date("d.m.Y",$dat['time']).'" onClick="seite(\'training\',\''.$dat['id'].'\')">
				'.$code.'
			</span>
		</td>');
	}
	for ($i = $i; $i < 10; $i++) { echo('
		<td>
			&nbsp;
		</td>'); }
	echo('
	</tr>');
} 
?>
	<tr class="space">
		<td colspan="11">
		</td>
	</tr>
</table>
<?php
endforeach;
?>

<table cellspacing="0" width="100%" class="small">
	<tr class="b">
		<td colspan="11">
			Trainingsreichsten Laufphasen
		</td>
	</tr>
	<tr class="space">
		<td colspan="11">
		</td>
	</tr>
	<tr class="a1 r">
		<td class="c b">
			Jahre
		</td>
<?php # Jahre
$i = 0;
$jahr_db = mysql_query('SELECT `sportid`, SUM(`distanz`) as `km`, YEAR(FROM_UNIXTIME(`time`)) as `year` FROM `ltb_training` WHERE `sportid`=1 GROUP BY `year` ORDER BY `km` DESC LIMIT 10');
while ($jahr = mysql_fetch_assoc($jahr_db)) { $i++;
	$link = 'daten(\''.mktime(0,0,0,1,1,$jahr['year']).'\',\''.mktime(0,0,0,1,1,$jahr['year']).'\',\''.mktime(23,59,50,12,31,$jahr['year']).'\');';
	echo('
		<td>
			<span class="link" title="'.$jahr['year'].'" onclick="'.$link.'">
				'.km($jahr['km']).'
			</span>
		</td>');
}
for ($i; $i < 10; $i++) { echo('
		<td>
		</td>'); }
?>
	</tr>

	<tr class="a2 r">
		<td class="c b">
			Monate
		</td>
<?php # Monate
$i = 0;
$monat_db = mysql_query('SELECT `sportid`, SUM(`distanz`) as `km`, YEAR(FROM_UNIXTIME(`time`)) as `year`, MONTH(FROM_UNIXTIME(`time`)) as `month`, (MONTH(FROM_UNIXTIME(`time`))+100*YEAR(FROM_UNIXTIME(`time`))) as `monthyear` FROM `ltb_training` WHERE `sportid`=1 GROUP BY `monthyear` ORDER BY `km` DESC LIMIT 10');
while ($monat = mysql_fetch_assoc($monat_db)) { $i++;
	$link = 'daten(\''.mktime(0,0,0,$monat['month'],1,$monat['year']).'\',\''.mktime(0,0,0,$monat['month'],1,$monat['year']).'\',\''.mktime(23,59,50,$monat['month']+1,0,$monat['year']).'\');';
	echo('
		<td>
			<span class="link" title="'.monat($monat['month']).' '.$monat['year'].'" onclick="'.$link.'">
				'.km($monat['km']).'
			</span>
		</td>');
}
for ($i; $i < 10; $i++) { echo('
		<td>
		</td>'); }
?>
	</tr>

	<tr class="a1 r">
		<td class="c b">
			Wochen
		</td>
<?php # Wochen
$i = 0;
$woche_db = mysql_query('SELECT `sportid`, SUM(`distanz`) as `km`, WEEK(FROM_UNIXTIME(`time`),1) as `week`, YEAR(FROM_UNIXTIME(`time`)) as `year`, YEARWEEK(FROM_UNIXTIME(`time`),1) as `weekyear`, `time` FROM `ltb_training` WHERE `sportid`=1 GROUP BY `weekyear` ORDER BY `km` DESC LIMIT 10');
while ($woche = mysql_fetch_assoc($woche_db)) { $i++;
	$link = 'daten(\''.$woche['time'].'\',\''.wochenstart($woche['time']).'\',\''.wochenende($woche['time']).'\');';
	echo('
		<td>
			<span class="link" title="KW '.$woche['week'].' '.$woche['year'].'" onclick="'.$link.'">
				'.km($woche['km']).'
			</span>
		</td>');
}
echo(mysql_error());
for ($i; $i < 10; $i++) { echo('
		<td>
		</td>'); }
?>
	</tr>
	<tr class="space">
		<td colspan="11">
		</td>
	</tr>
</table>