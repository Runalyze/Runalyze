<?php
/**
 * This file contains the plugin "Statistiken".
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Stat ($this)
 * @uses class::Mysql
 * @uses class::Helper
 * @uses class:JD
 * @uses CONFIG_SHOW_RECHENSPIELE
 * @uses START_YEAR
 *
 * Last modified 2010/08/27 21:41 by Hannes Christiansen
 */
/**
 * Plugin-installer, will be called by class::Plugin for installing this plugin.
 */
function stat_statistiken_installer() {
	$type = 'stat';
	$filename = 'stat.statistiken.inc.php';
	$name = 'Statistiken';
	$description = 'Allgemeine Statistiken: Monatszusammenfassung in der Jahres&uuml;bersicht f&uuml;r alle Sportarten.';
	// TODO Include the plugin-installer
}

$Mysql = Mysql::getInstance();

$sport = $Mysql->fetch('ltb_sports', $this->sportid);
?>
<h1>
	<?php echo $sport['name']; ?>
	<?php echo ($this->year != -1) ? $this->year : 'Jahresvergleich'; ?>
</h1>

<small class="left">
<?php
$sports = $Mysql->fetchAsArray('SELECT `name`, `id` FROM `ltb_sports` ORDER BY `id` ASC');
foreach ($sports as $i => $sportlink) {
	if ($i != 0)
		echo(' |'.NL);
	echo $this->getInnerLink($sportlink['name'], $sportlink['id'], $this->year);
}
?>
</small>

<small class="right">
<?php
for ($x = START_YEAR; $x <= date("Y"); $x++)
	echo $this->getInnerLink($x, $this->sportid, $x).' | ';

echo $this->getInnerLink('Jahresvergleich', $this->sportid, -1);
?>
</small>

<br class="clear" />

<table style="width:100%;" class="small r">
	<tr class="b c">
		<td />
<?php
if ($this->year != -1):
	$num_i = 12;
	$num_start = 1;
?>
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
<?php
else:
	$num_i = date("Y") - START_YEAR;
	$num_start = START_YEAR;
	for ($i = START_YEAR; $i <= date("Y"); $i++)
		echo('		<td>'.$i.'</td>'.NL);
endif;

$line = 0;
?>
	</tr>
	<tr class="space"><td colspan="13" /></tr>
<?php ////////// LINE: Stunden ?>
	<tr class="a<?php echo ($line%2+1);?>">
		<td class="b">Stunden</td>
<?php
$num_i_i = 0;
$data = ($this->year != -1)
	? $Mysql->fetchAsArray('SELECT SUM(`dauer`) as `dauer`, MONTH(FROM_UNIXTIME(`time`)) as `i` FROM `ltb_training` WHERE `sportid`='.$this->sportid.' && YEAR(FROM_UNIXTIME(`time`))='.$this->year.' GROUP BY MONTH(FROM_UNIXTIME(`time`)) ORDER BY `i` LIMIT 12')
	: $Mysql->fetchAsArray('SELECT SUM(`dauer`) as `dauer`, YEAR(FROM_UNIXTIME(`time`)) as `i` FROM `ltb_training` WHERE `sportid`='.$this->sportid.' GROUP BY YEAR(FROM_UNIXTIME(`time`)) ORDER BY `i`');
if (count($data) > 0) {
	foreach($data as $i => $dat) {
		// Fill empty columns
		for (; ($num_start+$num_i_i) < $dat['i']; $num_i_i++)
			echo('		<td>&nbsp;</td>'.NL);
		$num_i_i++;

		// Print data
		echo ($dat['dauer'] != 0)
			? ('		<td>'.Helper::Time($dat['dauer'], false).'</td>'.NL)
			: ('		<td>&nbsp;</td>'.NL);
	}

	// Fill empty columns
	for (; $num_i_i < $num_i; $num_i_i++)
		echo('		<td>&nbsp;</td>'.NL);
} else
	echo('		<td colspan="'.$num_i.'" />'.NL);
?>  
	</tr>
	<tr class="space"><td colspan="13" /></tr>
<?php ////////// LINE: Kilometer ?>
<?php if ($sport['distanztyp'] != 0): $line++; ?>
	<tr class="a<?php echo ($line%2+1);?>">
		<td class="b">KM</td>
	<?php
	$num_i_i = 0;
	$data = ($this->year != -1)
		? $Mysql->fetchAsArray('SELECT SUM(`distanz`) as `distanz`, MONTH(FROM_UNIXTIME(`time`)) as `i` FROM `ltb_training` WHERE `sportid`='.$this->sportid.' && YEAR(FROM_UNIXTIME(`time`))='.$this->year.' GROUP BY MONTH(FROM_UNIXTIME(`time`)) ORDER BY `i` LIMIT 12')
		: $Mysql->fetchAsArray('SELECT SUM(`distanz`) as `distanz`, YEAR(FROM_UNIXTIME(`time`)) as `i` FROM `ltb_training` WHERE `sportid`='.$this->sportid.' GROUP BY YEAR(FROM_UNIXTIME(`time`)) ORDER BY `i`');
	if (count($data) > 0) {
		foreach($data as $i => $dat) {
			// Fill empty columns
			for (; ($num_start+$num_i_i) < $dat['i']; $num_i_i++)
				echo Helper::emptyTD();
			$num_i_i++;
	
			// Print data
			echo ($dat['distanz'] != 0)
				? ('		<td>'.Helper::Km($dat['distanz'], 0).'</td>'.NL)
				: Helper::emptyTD();
		}
		// Fill empty columns
		for (; $num_i_i < $num_i; $num_i_i++)
			echo Helper::emptyTD();
	} else
		echo('		<td colspan="'.$num_i.'" />'.NL);
	?>
	</tr>
	<tr class="space"><td colspan="13" /></tr>
<?php endif; ?>
<?php ////////// LINE: Tempo ?>
<?php if ($sport['distanztyp'] != 0): $line++; ?>
	<tr class="a<?php echo ($line%2+1);?>">
		<td class="b">&Oslash;Tempo</td>
	<?php
	$num_i_i = 0;
	$data = ($this->year != -1)
		? $Mysql->fetchAsArray('SELECT SUM(`distanz`) as `distanz`, SUM(`dauer`) as `dauer`, MONTH(FROM_UNIXTIME(`time`)) as `i` FROM `ltb_training` WHERE `sportid`='.$this->sportid.' && YEAR(FROM_UNIXTIME(`time`))='.$this->year.' GROUP BY MONTH(FROM_UNIXTIME(`time`)) ORDER BY `i` LIMIT 12')
		: $Mysql->fetchAsArray('SELECT SUM(`distanz`) as `distanz`, SUM(`dauer`) as `dauer`, YEAR(FROM_UNIXTIME(`time`)) as `i` FROM `ltb_training` WHERE `sportid`='.$this->sportid.' GROUP BY YEAR(FROM_UNIXTIME(`time`)) ORDER BY `i`');
	if (count($data) > 0) {
		foreach($data as $i => $dat) {
			// Fill empty columns
			for (; ($num_start+$num_i_i) < $dat['i']; $num_i_i++)
				echo Helper::emptyTD();
			$num_i_i++;
	
			// Print data
			echo ($dat['dauer'] != 0)
				? ('		<td>'.Helper::Speed($dat['distanz'], $dat['dauer'], $this->sportid).'</td>'.NL)
				: Helper::emptyTD();
		}
		// Fill empty columns
		for (; $num_i_i < $num_i; $num_i_i++)
			echo Helper::emptyTD();
	} else
		echo('		<td colspan="'.$num_i.'" />'.NL);
	?>
	</tr>
	<tr class="space"><td colspan="13" /></tr>
<?php endif; ?>
<?php ////////// LINE: VDOT ?>
<?php if ($this->sportid == 1 && CONFIG_SHOW_RECHENSPIELE != 0): $line++; ?>
	<tr class="a<?php echo ($line%2+1);?>">
		<td class="b">VDOT</td>
	<?php
	if ($this->year != -1) {
		$date = 'MONTH';
		$for_start = 1;
		$for_end = 12;
	} else {
		$date = 'YEAR';
		$for_start = START_YEAR;
		$for_end = date("Y");
	}

	for ($i = $for_start; $i <= $for_end; $i++) {
		$VDOT = 0;
		$num = 0;
		$data = ($date == 'MONTH')
			? $Mysql->fetch('SELECT AVG(`vdot`) as `vdot` FROM `ltb_training` WHERE `sportid`='.$this->sportid.' && `puls`!=0 && YEAR(FROM_UNIXTIME(`time`))='.$this->year.' && MONTH(FROM_UNIXTIME(`time`))='.$i.' GROUP BY `sportid` LIMIT 1')
			: $Mysql->fetch('SELECT AVG(`vdot`) as `vdot` FROM `ltb_training` WHERE `sportid`='.$this->sportid.' && `puls`!=0 && YEAR(FROM_UNIXTIME(`time`))='.$i.' GROUP BY `sportid` LIMIT 1');
		if ($data !== false)
			$VDOT = JD::correctVDOT($data['vdot']);
		else
			$VDOT = 0;
		echo ($VDOT != 0)
			? ('		<td>'.number_format($VDOT, 1).'</td>'.NL)
			: Helper::emptyTD();
	}
	?>
	</tr>
	<tr class="space"><td colspan="13" /></tr>
<?php endif; ?>
<?php ////////// LINE: TRIMP ?>
<?php if (CONFIG_SHOW_RECHENSPIELE != 0): $line++; ?>
	<tr class="a<?php echo ($line%2+1);?>">
		<td class="b">TRIMP</td>
	<?php
	$num_i_i = 0;
	$data = ($this->year != -1)
		? $Mysql->fetchAsArray('SELECT SUM(`trimp`) as `trimp`, MONTH(FROM_UNIXTIME(`time`)) as `i` FROM `ltb_training` WHERE `sportid`='.$this->sportid.' && YEAR(FROM_UNIXTIME(`time`))='.$this->year.' GROUP BY MONTH(FROM_UNIXTIME(`time`)) ORDER BY `i` LIMIT 12')
		: $Mysql->fetchAsArray('SELECT SUM(`trimp`) as `trimp`, YEAR(FROM_UNIXTIME(`time`)) as `i` FROM `ltb_training` WHERE `sportid`='.$this->sportid.' GROUP BY YEAR(FROM_UNIXTIME(`time`)) ORDER BY `i`');
	if (count($data) > 0) {
		foreach($data as $i => $dat) {
			// Fill empty columns
			for (; ($num_start+$num_i_i) < $dat['i']; $num_i_i++)
				echo Helper::emptyTD();
			$num_i_i++;
	
			// Print data
			$avg_num = ($this->year != -1) ? 15 : 180;
			echo ($dat['trimp'] != 0)
				? ('		<td style="color:#'.Helper::Stresscolor($dat['trimp']/$avg_num).'">'.$dat['trimp'].'</td>'.NL)
				: Helper::emptyTD();
		}
		// Fill empty columns
		for (; $num_i_i < $num_i; $num_i_i++)
			echo Helper::emptyTD();
	} else
		echo('		<td colspan="'.$num_i.'" />'.NL);
	?>
	</tr>
	<tr class="space"><td colspan="13" /></tr>
<?php endif; ?>
</table>