<?php
/**
 * This file contains the plugin "Lauf-ABC".
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Stat ($this)
 * @uses class::Mysql
 * @uses class::Error
 *
 * Last modified 2010/09/04 23:27 by Hannes Christiansen
 */
/**
 * Plugin-installer, will be called by class::Plugin for installing this plugin.
 */
function stat_laufabc_installer() {
	$type = 'stat';
	$filename = 'stat.laufabc.inc.php';
	$name = 'Lauf-ABC';
	$description = 'Wie oft hast du Lauf-ABC absolviert?';
	// TODO Include the plugin-installer
}

$Mysql = Mysql::getInstance();
?>
<h1>Lauf-ABC</h1>

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
Error::getInstance()->addTodo('Set up correct search-link', __FILE__, __LINE__);

$ABCData = array();
$result = $Mysql->fetchAsArray('
	SELECT
		SUM(`laufabc`) as `abc`,
		SUM(1) as `num`,
		YEAR(FROM_UNIXTIME(`time`)) as `year`,
		MONTH(FROM_UNIXTIME(`time`)) as `month`
	FROM `ltb_training`
	GROUP BY `year`, `month`');

foreach ($result as $dat) {
	if ($dat['abc'] > 0)
		$ABCData[$dat['year']][$dat['month']] = array('abc' => $dat['abc'], 'num' => $dat['num']);
}

foreach ($ABCData as $y => $Data):
?>
	<tr class="a<?php echo($y%2+1); ?> r">
		<td class="b l">
			<?php echo $y; ?>
		</td>
		<?php
		for ($m = 1; $m <= 12; $m++) {
			if ($Data[$m]['abc'] > 0) {
				$link = '<span class="link" onclick="submit_suche(\'sort=DESC&order=hm&time-gt=01.'.$m.'.'.$y.'&time-lt=00.'.($m+1).'.'.$y.'\')" title="'.$Data[$m]['abc'].'x">'.round(100*$Data[$m]['abc']/$Data[$m]['num']).' &#37;</span>';
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