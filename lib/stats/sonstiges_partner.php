<h1>Trainingspartner</h1>

<table cellspacing="0" width="95%" style="margin:0 5px;" class="small">
	<tr class="b c">
		<td colspan="2">Alle Trainingspartner</td>
	</tr>
	<tr class="space">
		<td colspan="2" />
	</tr>
<?php
$partner = array();
$db = mysql_query('SELECT `trainingspartner` FROM `ltb_training` WHERE `trainingspartner` != ""');
while ($partner_dat = mysql_fetch_assoc($db)) {
$trainingspartner = explode(", ", $partner_dat['trainingspartner']);
foreach ($trainingspartner as $name) {
	if (!isset($partner[$name])) $partner[$name] = 1;
	else $partner[$name]++;
}
}

$num_x = 0;
$i = 0;
array_multisort($partner, SORT_DESC);

foreach ($partner as $name => $num) {
	if ($num_x != $num) {
		if ($num != 1) echo('
		</td>
	</tr>');
		$num_x = $num;
		$i++;
		echo('
	<tr class="a'.($i%2+1).'">
		<td class="b">'.$num.'x</td>
		<td>');
	}
	else echo(', ');
	echo('<span class="link" onclick="submit_suche(\'opt[trainingspartner]=like&val[trainingspartner]='.$name.'\')">'.$name.'</span>');
}
echo('
		</td>
	</tr>');
?>
</table>