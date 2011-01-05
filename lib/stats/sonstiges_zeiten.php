<h1>Trainingszeiten</h1>

<table cellspacing="0" width="98%" style="margin:0 5px 25px 5px;" class="left small">
	<tr class="b c">
		<td colspan="8">N&auml;chtliches Training</td>
	</tr>
<?php
$i = 0;
$sports = '';
$sports_db = mysql_query('SELECT `id` FROM `ltb_sports` WHERE `short` = 0');
while ($sports_dat = mysql_fetch_assoc($sports_db))
	$sports .= $sports_dat['id'].',';
$nacht_db = mysql_query('SELECT * FROM (
SELECT *, HOUR(FROM_UNIXTIME(`time`)) as `H`, MINUTE(FROM_UNIXTIME(`time`)) as `MIN`
FROM `ltb_training` WHERE `sportid` IN('.substr($sports,0,-1).') AND
(HOUR(FROM_UNIXTIME(`time`))!=0 OR MINUTE(FROM_UNIXTIME(`time`))!=0)
ORDER BY ABS(6-(`H`+3)%24-`MIN`/60) ASC, `MIN` DESC LIMIT 20
) t ORDER BY (`H`+12)%24 ASC, `MIN` ASC');
while ($nacht = mysql_fetch_assoc($nacht_db)):
	$i++;
	$sport = sport($nacht['sportid'],true);
?>
<?php if ($i%2 == 1): ?>
	<tr class="a<?php echo(round($i/2)%2+1); ?>">
<?php endif; ?>
		<td class="b"><?php echo date("H:i",$nacht['time']); ?> Uhr</td>
		<td><img class="link" onclick="seite('training','<?php echo $nacht['id']; ?>')" title="<?php echo $sport['name']; ?>" src="img/sports/<?php echo $sport['bild']; ?>" /></td>
		<td><?php echo ($nacht['distanz'] != 0 ? km($nacht['distanz']) : dauer($nacht['dauer'])).' '.$sport['name']; ?></td>
		<td><a href="#" onclick="daten('<?php echo $nacht['time']; ?>','<?php echo wochenstart($nacht['time']); ?>','<?php echo wochenende($nacht['time']); ?>')"><?php echo date("d.m.Y",$nacht['time']); ?></a></td>
<?php if ($i%2 == 0): ?>
	</tr>
<?php endif; ?>
<?php
endwhile;
?>
</table>

<img class="right" src="lib/draw/trainingstage.php" />
<img class="left" src="lib/draw/trainingszeiten.php" />

<br class="clear" />
&nbsp;