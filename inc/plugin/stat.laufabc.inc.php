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
for ($y = START_YEAR; $y <= date("Y"); $y++):
	if ($Mysql->num('SELECT 1 FROM `ltb_training` WHERE YEAR(FROM_UNIXTIME(`time`))="'.$y.'" AND `laufabc`!=0 LIMIT 1') > 0):
?>
	<tr class="a<?php echo($y%2+1); ?> r">
		<td class="b l">
			<?php echo $y; ?>
		</td>
<?php
		for ($m = 1; $m <= 12; $m++) {
			$month = $Mysql->fetch('SELECT SUM(`laufabc`) as `abc`, SUM(1) as `num`, 1 as `group` FROM `ltb_training` WHERE YEAR(FROM_UNIXTIME(`time`))="'.$y.'" AND MONTH(FROM_UNIXTIME(`time`))="'.$m.'" GROUP BY `group` LIMIT 1');
			if ($month === false)
				echo('
				<td>&nbsp;</td>');
			else {
				$link = '<span class="link" onclick="submit_suche(\'opt[laufabc]=is&val[laufabc]=1&time-gt=01.'.$m.'.'.$y.'&time-lt=00.'.($m+1).'.'.$y.'\')" title="'.$month['abc'].'x">'.round(100*$month['abc']/$month['num']).' &#37;</span>';
				echo('
				<td>
					'.($month['abc'] != 0 ? $link : '&nbsp;').'
				</td>');
			}
		}
	endif;
endfor;
?>
	</tr>
	<tr class="space">
		<td colspan="13">
		</td>
	</tr>
</table>