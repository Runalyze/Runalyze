<?php
/**
 * This file contains the plugin "Höhenhmeter".
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Stat ($this)
 * @uses class::Mysql ($mysql)
 * @uses class::Error ($error)
 * @uses class::Helper
 * @uses START_YEAR
 *
 * Last modified 2010/09/04 21:07 by Hannes Christiansen
 */
/**
 * Plugin-installer, will be called by class::Plugin for installing this plugin.
 */
function stat_hoehenmeter_installer() {
	$type = 'stat';
	$filename = 'stat.hoehenmeter.inc.php';
	$name = 'Höhenhmeter';
	$description = 'Die steilsten und bergigsten Läufe sowie der Überblick über die absolvierten Höhenmeter aller Monate.';
	// TODO Include the plugin-installer
}
?>
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
for ($y = START_YEAR; $y <= date("Y"); $y++):
	if ($mysql->num('SELECT 1 FROM `ltb_training` WHERE YEAR(FROM_UNIXTIME(`time`))="'.$y.'" AND `hm`!=0 LIMIT 1') > 0):
?>
	<tr class="a<?php echo($y%2+1); ?> r">
		<td class="b l">
			<?php echo $y; ?>
		</td>
		<?php
		for ($m = 1; $m <= 12; $m++) {
			$month = $mysql->fetch('SELECT SUM(`hm`) as `hmsum`, SUM(`distanz`) as `km`, 1 as `group` FROM `ltb_training` WHERE YEAR(FROM_UNIXTIME(`time`))="'.$y.'" AND MONTH(FROM_UNIXTIME(`time`))="'.$m.'" GROUP BY `group` LIMIT 1');
			if ($month !== false) {
				$link = '<span class="link" onclick="submit_suche(\'sort=DESC&order=hm&time-gt=01.'.$m.'.'.$y.'&time-lt=00.'.($m+1).'.'.$y.'\')" title="&oslash; '.round($month['hmsum']/$month['km']/10,2).' &#37;">'.$month['hmsum'].' hm</span>';
				echo('
				<td>
					'.($month['hmsum'] != 0 ? $link	: '&nbsp;').'
				</td>');
			}
			else
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
$strecken = $mysql->fetch('SELECT `time`, `sportid`, `id`, `hm`, `strecke`, `bemerkung` FROM `ltb_training` ORDER BY `hm` DESC LIMIT 10', false, true);
if ($strecken === false)
	echo('
	<tr>
		<td colspan="4"><em>Keine Strecken gefunden.</em></td>
	</tr>');
else
	foreach($strecken as $i => $strecke):
		$sport = Helper::Sport($strecke['sportid'],true);
?>
	<tr class="a<?php echo($i%2+1); ?>">
		<td class="small"><?php echo date("d.m.Y", $strecke['time']); ?></td>
		<td><img class="link" onclick="seite('training','<?php echo $strecke['id']; ?>')" title="<?php echo $sport['name']; ?>" src="img/sports/<?php echo $sport['bild']; ?>" /></td>
		<td title="<?php echo ($strecke['bemerkung'] != "" ? $strecke['bemerkung'].': ' : '').$strecke['strecke']; ?>"><?php echo $strecke['strecke']; ?></td>
		<td class="r"><?php echo $strecke['hm']; ?>&nbsp;hm</td>
	</tr>	
<?php endforeach; ?>
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
$strecken = $mysql->fetch('SELECT `time`, `sportid`, `id`, `hm`, `strecke`, `bemerkung`, (`hm`/`distanz`) as `steigung`, `distanz` FROM `ltb_training` ORDER BY `steigung` DESC LIMIT 10', false, true);
if ($strecken === false)
	echo('
	<tr>
		<td colspan="4"><em>Keine Strecken gefunden.</em></td>
	</tr>');
else
	foreach($strecken as $i => $strecke):
?>
	<tr class="a<?php echo($i%2+1); ?>">
		<td class="small"><?php echo date("d.m.Y", $strecke['time']); ?></td>
		<td><img class="link" onclick="seite('training','<?php echo $strecke['id']; ?>')" title="<?php echo $sport['name']; ?>" src="img/sports/<?php echo $sport['bild']; ?>" /></td>
		<td title="<?php echo ($strecke['bemerkung'] != "" ? $strecke['bemerkung'].': ' : '').$strecke['strecke']; ?>"><?php echo $strecke['strecke']; ?></td>
		<td class="r"><?php echo round($strecke['steigung']/10,2); ?>&nbsp;&#37;<br /><small>(<?php echo($strecke['hm'].'&nbsp;hm/'.$strecke['distanz'].'&nbsp;km'); ?>)</small></td>
	</tr>	
<?php endforeach; ?>
	<tr class="space">
		<td colspan="4">
		</td>
	</tr>
</table>

<br class="clear" />&nbsp;