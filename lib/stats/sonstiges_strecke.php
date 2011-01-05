<h1>Strecken</h1>

<table cellspacing="0" width="70%" style="margin:0 5px;" class="left small">
	<tr class="b c">
		<td colspan="3">H&auml;ufigsten Strecken</td>
	</tr>
	<tr class="space">
		<td colspan="3" />
	</tr>
<?php // Häufigsten Strecken
$x = 1;
$db = mysql_query('SELECT `strecke`, SUM(`distanz`) as `km`, SUM(1) as `num` FROM `ltb_training` WHERE `strecke`!="" GROUP BY `strecke` ORDER BY `num` DESC LIMIT 10');
while ($strecke = mysql_fetch_assoc($db)): $x++;
?>
	<tr class="a<?php echo($x%2+1); ?> r">
		<td><?php echo($strecke['num']); ?>x</td>
		<td class="l">
			<span class="link" onclick="submit_suche('opt[strecke]=like&val[strecke]=<?php echo($strecke['strecke']); ?>')" title="<?php echo($strecke['strecke']); ?>">
				<?php echo cut($strecke['strecke'],100); ?>
			</span>
		</td>
		<td><?php echo km($strecke['km']); ?></td>
	</tr>
<?php endwhile; ?>
</table>

<table cellspacing="0" width="25%" style="margin:0 5px;" class="left small">
	<tr class="b c">
		<td colspan="2">H&auml;ufigsten Orte</td>
	</tr>
	<tr class="space">
		<td colspan="2" />
	</tr>
<?php // Häufigsten Orte
$orte = array();
$db = mysql_query('SELECT `strecke`, `distanz` FROM `ltb_training` WHERE `strecke`!=""');
while ($strecke = mysql_fetch_assoc($db)) {
$strecken = explode(" - ", $strecke['strecke']);
foreach ($strecken as $streckenort) {
	if (!isset($orte[$streckenort])) $orte[$streckenort] = 1;
	else $orte[$streckenort]++;
}
}

array_multisort($orte, SORT_DESC);

$x = 1;
foreach ($orte as $ort => $num): $x++; ?>
	<tr class="a<?php echo($x%2+1); ?>">
		<td><?php echo($num); ?>x</td>
		<td><span class="link" onclick="submit_suche('opt[strecke]=like&val[strecke]=<?php echo($ort); ?>')"><?php echo($ort); ?></span></td>
	</tr>
<?php
if ($x == 11) break;
endforeach;
?>
</table>

<br class="clear" />
<br />

<table cellspacing="0" width="95%" style="margin:0 5px;" class="small">
	<tr class="b c">
		<td colspan="2">Seltensten Orte</td>
	</tr>
	<tr class="space">
		<td colspan="2" />
	</tr>
<?php // Seltensten Orte
$num_x = 0;
array_multisort($orte);

foreach ($orte as $ort => $num) {
if ($num_x <= 4) {
	if ($num_x != $num) {
		if ($num != 1) echo('
		</td>
	</tr>');
		$num_x = $num;
		echo('
	<tr class="a'.($num_x%2+1).'">
		<td class="b">'.$num.'x</td>
		<td>');
	}
	else echo(', ');
	echo('<span class="link" onclick="submit_suche(\'opt[strecke]=like&val[strecke]='.$ort.'\')">'.$ort.'</span>');
}
else {
	echo('
		</td>
	</tr>');
	break;
}
}
?>
	<tr class="a<?php echo(($x+1)%2+1); ?>">
		<td colspan="2" class="c">
			Insgesamt wurden <strong><?php echo sizeof($orte); ?> verschiedene Orte</strong> sportlich besucht.
		</td>
	</tr>
</table>