<?php
/**
 * This file contains the plugin "Strecken".
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Stat ($this)
 * @uses class::Mysql
 * @uses class::Helper
 *
 * Last modified 2010/09/03 21:01 by Hannes Christiansen
 */
/**
 * Plugin-installer, will be called by class::Plugin for installing this plugin.
 */
function stat_strecken_installer() {
	$type = 'stat';
	$filename = 'stat.strecken.inc.php';
	$name = 'Strecken';
	$description = 'Auflistung der häufigsten und seltensten Strecken/Orte.';
	// TODO Include the plugin-installer
}

$Mysql = Mysql::getInstance();
?>
<h1>Strecken</h1>

<table cellspacing="0" width="70%" style="margin:0 5px;" class="left small">
	<tr class="b c">
		<td colspan="3">H&auml;ufigsten Strecken</td>
	</tr>
	<tr class="space">
		<td colspan="3" />
	</tr>
<?php
Error::getInstance()->addTodo('Set correct onclick-link', __FILE__, __LINE__);
// Häufigsten Strecken
$strecken = $Mysql->fetch('SELECT `strecke`, SUM(`distanz`) as `km`, SUM(1) as `num` FROM `ltb_training` WHERE `strecke`!="" GROUP BY `strecke` ORDER BY `num` DESC LIMIT 10', false, true);
foreach ($strecken as $i => $strecke):
?>
	<tr class="a<?php echo($i%2+1); ?> r">
		<td><?php echo($strecke['num']); ?>x</td>
		<td class="l">
			<span class="link" onclick="submit_suche('opt[strecke]=like&val[strecke]=<?php echo($strecke['strecke']); ?>')" title="<?php echo($strecke['strecke']); ?>">
				<?php echo Helper::Cut($strecke['strecke'],100); ?>
			</span>
		</td>
		<td><?php echo Helper::Km($strecke['km']); ?></td>
	</tr>
<?php endforeach; ?>
</table>

<table cellspacing="0" width="25%" style="margin:0 5px;" class="left small">
	<tr class="b c">
		<td colspan="2">H&auml;ufigsten Orte</td>
	</tr>
	<tr class="space">
		<td colspan="2" />
	</tr>
<?php
// Häufigsten Orte
$orte = array();
$strecken = $Mysql->fetch('SELECT `strecke`, `distanz` FROM `ltb_training` WHERE `strecke`!=""', false, true);
foreach ($strecken as $strecke) {
	$streckenorte = explode(" - ", $strecke['strecke']);
	foreach ($streckenorte as $streckenort) {
		if (!isset($orte[$streckenort]))
			$orte[$streckenort] = 1;
		else
			$orte[$streckenort]++;
	}
}

array_multisort($orte, SORT_DESC);

$i = 1;
foreach ($orte as $ort => $num): $i++; ?>
	<tr class="a<?php echo($i%2+1); ?>">
		<td><?php echo($num); ?>x</td>
		<td><span class="link" onclick="submit_suche('opt[strecke]=like&val[strecke]=<?php echo($ort); ?>')"><?php echo($ort); ?></span></td>
	</tr>
<?php
	if ($i == 11) break;
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
<?php
// Seltensten Orte
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
	<tr class="a<?php echo(($num_x+1)%2+1); ?>">
		<td colspan="2" class="c">
			Insgesamt wurden <strong><?php echo count($orte); ?> verschiedene Orte</strong> sportlich besucht.
		</td>
	</tr>
</table>