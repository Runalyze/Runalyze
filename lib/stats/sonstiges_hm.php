<h1>H&ouml;henmeter</h1>

<table cellspacing="0" width="100%" class="small">
	<tr class="b">
		<td />
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
		<td colspan="13">
		</td>
	</tr>
<?php
for ($jahr = $config['startjahr']; $jahr <= date("Y"); $jahr++):
	if (mysql_num_rows(mysql_query('SELECT 1 FROM `ltb_training` WHERE YEAR(FROM_UNIXTIME(`time`))="'.$jahr.'" AND `hm`!=0 LIMIT 1')) > 0):
?>
	<tr class="a<?php echo($jahr%2+1); ?> r">
		<td class="b l">
			<?php echo $jahr; ?>
		</td>
<?php
for ($m = 1; $m <= 12; $m++) {
	$monat_db = mysql_query('SELECT SUM(`hm`) as `hmsum`, SUM(`distanz`) as `km`, 1 as `group` FROM `ltb_training` WHERE YEAR(FROM_UNIXTIME(`time`))="'.$jahr.'" AND MONTH(FROM_UNIXTIME(`time`))="'.$m.'" GROUP BY `group` LIMIT 1');
	while ($monat = mysql_fetch_assoc($monat_db)) {
		$link = '<span class="link" onclick="submit_suche(\'sort=DESC&order=hm&time-gt=01.'.$m.'.'.$jahr.'&time-lt=00.'.($m+1).'.'.$jahr.'\')" title="&oslash; '.round($monat['hmsum']/$monat['km']/10,2).' &#37;">'.$monat['hmsum'].' hm</span>';
		echo('
		<td>
			'.($monat['hmsum'] != 0 ? $link	: '&nbsp;').'
		</td>');
	}
	if (mysql_num_rows($monat_db) == 0)
		echo('
		<td>&nbsp;</td>');
}
?>
<?php
	endif;
endfor;
?>
	</tr>
	<tr class="space">
		<td colspan="13">
		</td>
	</tr>
</table>

<table cellspacing="0" width="48%" style="margin:0 5px;" class="left small">
	<tr class="b c">
		<td colspan="3">Meisten H&ouml;henmeter</td>
	</tr>
	<tr class="space">
		<td colspan="4" />
	</tr>
<?php
$x = 1;
$strecken_db = mysql_query('SELECT `time`, `sportid`, `id`, `hm`, `strecke`, `bemerkung` FROM `ltb_training` ORDER BY `hm` DESC LIMIT 10');
while ($strecke = mysql_fetch_assoc($strecken_db)): $x++;
	$sport = sport($strecke['sportid'],true);
?>
	<tr class="a<?php echo($x%2+1); ?>">
		<td class="small"><?php echo date("d.m.Y", $strecke['time']); ?></td>
		<td><img class="link" onclick="seite('training','<?php echo $strecke['id']; ?>')" title="<?php echo $sport['name']; ?>" src="img/sports/<?php echo $sport['bild']; ?>" /></td>
		<td title="<?php echo ($strecke['bemerkung'] != "" ? $strecke['bemerkung'].': ' : '').$strecke['strecke']; ?>"><?php echo $strecke['strecke']; ?></td>
		<td class="r"><?php echo $strecke['hm']; ?>&nbsp;hm</td>
	</tr>	
<?php
endwhile;
?>
	<tr class="space">
		<td colspan="4">
		</td>
	</tr>
</table>

<table cellspacing="0" width="48%" style="margin:0 5px;" class="right small">
	<tr class="b c">
		<td colspan="3">Steilsten Strecken</td>
	</tr>
	<tr class="space">
		<td colspan="4" />
	</tr>
<?php
$x = 1;
$strecken_db = mysql_query('SELECT `time`, `sportid`, `id`, `hm`, `strecke`, `bemerkung`, (`hm`/`distanz`) as `steigung`, `distanz` FROM `ltb_training` ORDER BY `steigung` DESC LIMIT 10');
while ($strecke = mysql_fetch_assoc($strecken_db)): $x++;
	$sport = sport($strecke['sportid'],true);
?>
	<tr class="a<?php echo($x%2+1); ?>">
		<td class="small"><?php echo date("d.m.Y", $strecke['time']); ?></td>
		<td><img class="link" onclick="seite('training','<?php echo $strecke['id']; ?>')" title="<?php echo $sport['name']; ?>" src="img/sports/<?php echo $sport['bild']; ?>" /></td>
		<td title="<?php echo ($strecke['bemerkung'] != "" ? $strecke['bemerkung'].': ' : '').$strecke['strecke']; ?>"><?php echo $strecke['strecke']; ?></td>
		<td class="r"><?php echo round($strecke['steigung']/10,2); ?>&nbsp;&#37;<br /><small>(<?php echo($strecke['hm'].'&nbsp;hm/'.$strecke['distanz'].'&nbsp;km'); ?>)</small></td>
	</tr>	
<?php
endwhile;
?>
	<tr class="space">
		<td colspan="4">
		</td>
	</tr>
</table>

<br class="clear" />&nbsp;