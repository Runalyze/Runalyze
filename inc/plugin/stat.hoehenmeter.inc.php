<?php
/**
 * This file contains the plugin "Hoehenhmeter".
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Stat ($this)
 * @uses class::Mysql
 * @uses class::Error
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
	$name = 'H&ouml;henhmeter';
	$description = 'Die steilsten und bergigsten L&auml;ufe sowie der &Uuml;berblick &uuml;ber die absolvierten H&ouml;henmeter aller Monate.';
	// TODO Include the plugin-installer
}

$Mysql = Mysql::getInstance();
?>
<h1>H&ouml;henmeter</h1>

<table style="width:100%;" class="small">
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
$ElevationData = array();
$result = $Mysql->fetchAsArray('
	SELECT
		SUM(`hm`) as `hm`,
		SUM(`distanz`) as `km`,
		YEAR(FROM_UNIXTIME(`time`)) as `year`,
		MONTH(FROM_UNIXTIME(`time`)) as `month`
	FROM `ltb_training`
	WHERE `hm` > 0
	GROUP BY `year`, `month`');

foreach ($result as $dat) {
	$ElevationData[$dat['year']][$dat['month']] = array('hm' => $dat['hm'], 'km' => $dat['km']);
}

foreach ($ElevationData as $y => $Data):
?>
	<tr class="a<?php echo($y%2+1); ?> r">
		<td class="b l">
			<?php echo $y; ?>
		</td>
		<?php
		for ($m = 1; $m <= 12; $m++) {
			if ($Data[$m]['hm'] > 0) {
				$link = '<span class="link" onclick="submit_suche(\'sort=DESC&order=hm&time-gt=01.'.$m.'.'.$y.'&time-lt=00.'.($m+1).'.'.$y.'\')" title="&oslash; '.round($Data[$m]['hm']/$Data[$m]['km']/10,2).' &#37;">'.$Data[$m]['hm'].' hm</span>';
				echo('<td>'.$link.'</td>'.NL);
			}
			else
				echo Helper::emptyTD();
		}
		?>
<?php
endforeach;
?>
	</tr>
	<tr class="space">
		<td colspan="13">
		</td>
	</tr>
</table>

<table style="width:48%;" style="margin:0 5px;" class="left small">
	<tr class="b c">
		<td colspan="3">Meisten H&ouml;henmeter</td>
	</tr>
	<tr class="space">
		<td colspan="4" />
	</tr>
<?php
$strecken = $Mysql->fetchAsArray('SELECT `time`, `sportid`, `id`, `hm`, `strecke`, `bemerkung` FROM `ltb_training` ORDER BY `hm` DESC LIMIT 10');
if (count($strecken) == 0)
	echo('
	<tr>
		<td colspan="4"><em>Keine Strecken gefunden.</em></td>
	</tr>');
else
	foreach($strecken as $i => $strecke):
		$icon = Icon::getSportIcon($strecke['sportid']);
?>
	<tr class="a<?php echo($i%2+1); ?>">
		<td class="small"><?php echo date("d.m.Y", $strecke['time']); ?></td>
		<td><?php echo Ajax::trainingLink($strecke['id'], $icon); ?></td>
		<td title="<?php echo ($strecke['bemerkung'] != "" ? $strecke['bemerkung'].': ' : '').$strecke['strecke']; ?>"><?php echo $strecke['strecke']; ?></td>
		<td class="r"><?php echo $strecke['hm']; ?>&nbsp;hm</td>
	</tr>	
<?php endforeach; ?>
	<tr class="space">
		<td colspan="4">
		</td>
	</tr>
</table>

<table style="width:48%;" style="margin:0 5px;" class="right small">
	<tr class="b c">
		<td colspan="3">Steilsten Strecken</td>
	</tr>
	<tr class="space">
		<td colspan="4" />
	</tr>
<?php
Error::getInstance()->addTodo('Set up correct trainingLink', __FILE__, __LINE__);

$strecken = $Mysql->fetchAsArray('SELECT `time`, `sportid`, `id`, `hm`, `strecke`, `bemerkung`, (`hm`/`distanz`) as `steigung`, `distanz` FROM `ltb_training` ORDER BY `steigung` DESC LIMIT 10');
if (count($strecken) == 0)
	echo('
	<tr>
		<td colspan="4"><em>Keine Strecken gefunden.</em></td>
	</tr>');
else
	foreach($strecken as $i => $strecke):
		$icon = Icon::getSportIcon($strecke['sportid']);
?>
	<tr class="a<?php echo($i%2+1); ?>">
		<td class="small"><?php echo date("d.m.Y", $strecke['time']); ?></td>
		<td><?php echo Ajax::trainingLink($strecke['id'], $icon); ?></td>
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