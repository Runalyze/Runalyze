<?php
/**
 * This file contains the plugin "Analyse".
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Stat ($this)
 * @uses class::Mysql
 * @uses class::Helper
 * @uses START_YEAR
 * @uses HF_MAX
 *
 * Last modified 2010/08/09 19:41 by Hannes Christiansen
 */
/**
 * Plugin-installer, will be called by class::Plugin for installing this plugin.
 */
function stat_analyse_installer() {
	$type = 'stat';
	$filename = 'stat.analyse.inc.php';
	$name = 'Analyse';
	$description = 'Analyse des Trainings zum Tempo, der Distanz und den verschiedenen Trainingstypen.';
	// TODO Include the plugin-installer
}

$Mysql = Mysql::getInstance();
$Error = Error::getInstance();

$Error->add('TODO', 'Add plot for trainingtypes', __FILE__, __LINE__);
?>
<h1>Training <?php echo ($this->year != -1) ? $this->year : 'Jahresvergleich'; ?></h1>

<small class="right">
<?php
for ($x = START_YEAR; $x <= date("Y"); $x++) {
	echo $this->getInnerLink($x, 0, $x).' | ';
}
echo $this->getInnerLink('Jahresvergleich', 0, -1);
?>
</small>

<br class="clear" />

<table cellspacing="0" width="100%" class="small r">
	<tr class="b">
		<td>Trainingstypen</td>
<?php
if ($this->year != -1) {
	for ($i = 1; $i <= 12; $i++)
		echo('
		<td width="8%">'.Helper::Monat($i, true).'</td>');
} else {
	for ($i = START_YEAR; $i <= date("Y"); $i++)
		echo('		<td>'.$i.'</td>'.NL);
	echo('		<td>Gesamt</td>'.NL);
}
?>
	</tr>
	<tr class="space"><td colspan="13" /></tr>
<?php
$typen = $Mysql->fetch('SELECT * FROM `ltb_typ` ORDER BY `RPE` ASC', false, true);
foreach ($typen as $i => $typ):
?>
	<tr class="a<?php echo(($i%2+1));?>">
		<td class="b" title="<?php echo($typ['name']); ?>"><?php echo($typ['abk']); ?></td>
<?php
	if ($this->year != -1):
		$month_km = array();
		for ($m = 1; $m <= 12; $m++) {
			$month_dat = $Mysql->fetch('SELECT SUM(`distanz`) as `distanz` FROM `ltb_training` WHERE `sportid`=1 && YEAR(FROM_UNIXTIME(`time`))='.$this->year.' && MONTH(FROM_UNIXTIME(`time`))='.$m);
			$month_km[$m] = $month_dat['distanz'];
			$dat = $Mysql->fetch('SELECT COUNT(*) as `num`, SUM(`distanz`) as `distanz` FROM `ltb_training` WHERE `typid`='.$typ['id'].' && `sportid`=1 && YEAR(FROM_UNIXTIME(`time`))='.$this->year.' && MONTH(FROM_UNIXTIME(`time`))='.$m);
			echo ($dat['num'] != 0)
				? '<td title="'.$dat['num'].'x - '.Helper::Km($dat['distanz']).'">'.number_format(round(100*$dat['distanz']/$month_dat['distanz'],1),1).' &#37;</td>'
				: '<td>&nbsp;</td>';
		}
	else:
		$year_km = array();
		for ($y = START_YEAR; $y <= date("Y"); $y++) {
			$year_dat = $Mysql->fetch('SELECT SUM(`distanz`) as `distanz` FROM `ltb_training` WHERE `sportid`=1 && YEAR(FROM_UNIXTIME(`time`))='.$y);
			$year_km[$y] = $year_dat['distanz'];
			$dat = $Mysql->fetch('SELECT COUNT(*) as `num`, SUM(`distanz`) as `distanz` FROM `ltb_training` WHERE `typid`='.$typ['id'].' && `sportid`=1 && YEAR(FROM_UNIXTIME(`time`))='.$y);
			echo ($dat['num'] != 0)
				? '<td title="'.$dat['num'].'x - '.Helper::Km($dat['distanz']).'">'.number_format(round(100*$dat['distanz']/$year_dat['distanz'],1),1).' &#37;</td>'
				: '<td>&nbsp;</td>';
		}
	
		$all_dat = $Mysql->fetch('SELECT SUM(`distanz`) as `distanz` FROM `ltb_training` WHERE `sportid`=1');
		$dat = $Mysql->fetch('SELECT COUNT(*) as `num`, SUM(`distanz`) as `distanz` FROM `ltb_training` WHERE `typid`='.$typ['id'].' && `sportid`=1');
		echo ($dat['num'] != 0)
			? '<td title="'.$dat['num'].'x - '.Helper::Km($dat['distanz']).'">'.number_format(round(100*$dat['distanz']/$all_dat['distanz'],1),1).' &#37;</td>'
			: '<td>&nbsp;</td>';
	endif;
?>  
	</tr>
<?php endforeach; ?>
	<tr class="space"><td colspan="13" /></tr>
	<tr class="a<?php echo((($i+1)%2+1));?>">
		<td class="b">Gesamt</td>
<?php
if (isset($month_km))
	for ($m = 1; $m <= 12; $m++)
		echo('<td>'.Helper::Km($month_km[$m],0).'</td>');
else
	for ($y = START_YEAR; $y <= date("Y"); $y++)
		echo('<td>'.Helper::Km($year_km[$y],0).'</td>');
?>
	</tr>
</table>




<br class="clear" />




<table cellspacing="0" width="100%" class="small r">
	<tr class="b">
		<td>Pulsbereiche</td>
<?php
if ($this->year != -1) {
	for ($i = 1; $i <= 12; $i++)
		echo('
		<td width="8%">'.Helper::Monat($i, true).'</td>');
} else {
	for ($i = START_YEAR; $i <= date("Y"); $i++)
		echo('		<td>'.$i.'</td>'.NL);
	echo('		<td>Gesamt</td>'.NL);
}
?>
	</tr>
	<tr class="space"><td colspan="13" /></tr>
<?php
$Error->add('TODO', 'Puls-Analyse: Find in MySql via CEIL( ( 100 * `puls` /HF_MAX ) /5 ) AS `puls_group`', __FILE__, __LINE__);

$pulsbereiche = array(65, 70, 75, 80, 85, 90, 100);
foreach ($pulsbereiche as $i => $puls):
	// Add 0.01 to fix problems with >= / > and <= / <
	$puls_min = ($i == 0) ? 0 : HF_MAX*$pulsbereiche[$i-1]/100 + 0.01;
	$puls_max = HF_MAX*$puls/100 + 0.01;
?>
	<tr class="a<?php echo(($i%2+1));?>">
		<td class="b" title="<?php echo $puls_max; ?> bpm"><small>bis</small> <?php echo $puls; ?> &#37;</td>
<?php
	if ($this->year != -1):
		$month_km = array();
		for ($m = 1; $m <= 12; $m++) {
			$month_dat = $Mysql->fetch('SELECT SUM(`distanz`) as `distanz` FROM `ltb_training` WHERE `sportid`=1 && `puls`!=0 && YEAR(FROM_UNIXTIME(`time`))='.$this->year.' && MONTH(FROM_UNIXTIME(`time`))='.$m.' LIMIT 1');
			$month_km[$m] = $month_dat['distanz'];
			$dat = $Mysql->fetch('SELECT COUNT(*) as `num`, SUM(`distanz`) as `distanz` FROM `ltb_training` WHERE `puls`!=0 && `puls` BETWEEN '.$puls_min.' AND '.$puls_max.' && `sportid`=1 && YEAR(FROM_UNIXTIME(`time`))='.$this->year.' && MONTH(FROM_UNIXTIME(`time`))='.$m.' LIMIT 1');
			echo ($dat['num'] != 0)
				? '<td title="'.$dat['num'].'x - '.Helper::Km($dat['distanz']).'">'.number_format(round(100*$dat['distanz']/$month_dat['distanz'],1),1).' &#37;</td>'
				: '<td>&nbsp;</td>';
		}
	else:
		$year_km = array();
		for ($y = START_YEAR; $y <= date("Y"); $y++) {
			$year_dat = $Mysql->fetch('SELECT SUM(`distanz`) as `distanz` FROM `ltb_training` WHERE `sportid`=1 && `puls`!=0 && YEAR(FROM_UNIXTIME(`time`))='.$y.' LIMIT 1');
			$year_km[$y] = $year_dat['distanz'];
			$dat = $Mysql->fetch('SELECT COUNT(*) as `num`, SUM(`distanz`) as `distanz` FROM `ltb_training` WHERE `puls`!=0 && `puls` BETWEEN '.$puls_min.' AND '.$puls_max.' && `sportid`=1 && YEAR(FROM_UNIXTIME(`time`))='.$y.' LIMIT 1');
			echo ($dat['num'] != 0)
				? '<td title="'.$dat['num'].'x - '.Helper::Km($dat['distanz']).'">'.number_format(round(100*$dat['distanz']/$year_dat['distanz'],1),1).' &#37;</td>'
				: '<td>&nbsp;</td>';
		}
	
		$all_dat = $Mysql->fetch('SELECT SUM(`distanz`) as `distanz` FROM `ltb_training` WHERE `puls`!=0 && `sportid`=1');
		$dat = $Mysql->fetch('SELECT COUNT(*) as `num`, SUM(`distanz`) as `distanz` FROM `ltb_training` WHERE `puls`!=0 && `puls` BETWEEN '.$puls_min.' AND '.$puls_max.' && `sportid`=1');
		echo ($dat['num'] != 0)
			? '<td title="'.$dat['num'].'x - '.Helper::Km($dat['distanz']).'">'.number_format(round(100*$dat['distanz']/$all_dat['distanz'],1),1).' &#37;</td>'
			: '<td>&nbsp;</td>';
	endif;
?>  
	</tr>
<?php endforeach; ?>
</table>




<br class="clear" />




<table cellspacing="0" width="100%" class="small r">
	<tr class="b">
		<td>Tempobereiche</td>
<?php
if ($this->year != -1) {
	for ($i = 1; $i <= 12; $i++)
		echo('
		<td width="8%">'.Helper::Monat($i, true).'</td>');
} else {
	for ($i = START_YEAR; $i <= date("Y"); $i++)
		echo('		<td>'.$i.'</td>'.NL);
	echo('		<td>Gesamt</td>'.NL);
}
?>
	</tr>
	<tr class="space"><td colspan="13" /></tr>
<?php
$Error->add('TODO', 'Pace-Analyse: Find in MySql via CEIL( `pace_in_seconds` ) /15 ) AS `pace_group`', __FILE__, __LINE__);

$pace_start = 60*6 + 00;
$pace_end = 60*3 + 30;
for ($pace = $pace_start; $pace >= $pace_end; $pace -= 15):
	$pace_min = ($pace == $pace_start) ? INFINITY : $pace+15;
	$pace_max = ($pace == $pace_end) ? 0 : $pace;
	$tempo = ($pace == $pace_end) ? 'schneller' : '<small>bis</small> '.Helper::Tempo(1, $pace);
?>
	<tr class="a<?php echo(($i%2+1));?>">
		<td class="b"><?php echo $tempo; ?></td>
<?php
	if ($this->year != -1):
		$month_km = array();
		for ($m = 1; $m <= 12; $m++) {
			$month_dat = $Mysql->fetch('SELECT SUM(`distanz`) as `distanz` FROM `ltb_training` WHERE `sportid`=1 && YEAR(FROM_UNIXTIME(`time`))='.$this->year.' && MONTH(FROM_UNIXTIME(`time`))='.$m);
			$month_km[$m] = $month_dat['distanz'];
			$dat = $Mysql->fetch('SELECT COUNT(*) as `num`, SUM(`distanz`) as `distanz` FROM `ltb_training` WHERE (`dauer`/`distanz`) BETWEEN '.$pace_max.' AND '.$pace_min.' && `sportid`=1 && YEAR(FROM_UNIXTIME(`time`))='.$this->year.' && MONTH(FROM_UNIXTIME(`time`))='.$m);
			echo ($dat['num'] != 0)
				? '<td title="'.$dat['num'].'x - '.Helper::Km($dat['distanz']).'">'.number_format(round(100*$dat['distanz']/$month_dat['distanz'],1),1).' &#37;</td>'
				: '<td>&nbsp;</td>';
		}
	else:
		$year_km = array();
		for ($y = START_YEAR; $y <= date("Y"); $y++) {
			$year_dat = $Mysql->fetch('SELECT SUM(`distanz`) as `distanz` FROM `ltb_training` WHERE `sportid`=1 && YEAR(FROM_UNIXTIME(`time`))='.$y);
			$year_km[$y] = $year_dat['distanz'];
			$dat = $Mysql->fetch('SELECT COUNT(*) as `num`, SUM(`distanz`) as `distanz` FROM `ltb_training` WHERE (`dauer`/`distanz`) BETWEEN '.$pace_max.' AND '.$pace_min.' && `sportid`=1 && YEAR(FROM_UNIXTIME(`time`))='.$y);
			echo ($dat['num'] != 0)
				? '<td title="'.$dat['num'].'x - '.Helper::Km($dat['distanz']).'">'.number_format(round(100*$dat['distanz']/$year_dat['distanz'],1),1).' &#37;</td>'
				: '<td>&nbsp;</td>';
		}
	
		$all_dat = $Mysql->fetch('SELECT SUM(`distanz`) as `distanz` FROM `ltb_training` WHERE `sportid`=1');
		$dat = $Mysql->fetch('SELECT COUNT(*) as `num`, SUM(`distanz`) as `distanz` FROM `ltb_training` WHERE (`dauer`/`distanz`) BETWEEN '.$pace_max.' AND '.$pace_min.' && `sportid`=1');
		echo ($dat['num'] != 0)
			? '<td title="'.$dat['num'].'x - '.Helper::Km($dat['distanz']).'">'.number_format(round(100*$dat['distanz']/$all_dat['distanz'],1),1).' &#37;</td>'
			: '<td>&nbsp;</td>';
	endif;
?>  
	</tr>
<?php endfor; ?>
</table>