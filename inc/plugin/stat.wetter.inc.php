<?php
/**
 * This file contains the plugin "Wetter".
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Stat ($this)
 * @uses class::Mysql ($mysql)
 * @uses class::Helper
 * @uses START_YEAR
 *
 * Last modified 2011/01/08 18:42 by Michael Pohl
 */
/**
 * Plugin-installer, will be called by class::Plugin for installing this plugin.
 */
function stat_wettkampf_installer() {
	$type = 'stat';
	$filename = 'stat.wetter.inc.php';
	$name = 'Wetter';
	$description = 'Wetterverhältnisse, Temperaturen und die getragenen Kleidungsstücke.';
	// TODO Include the plugin-installer
}

$error->add('TODO', 'Change via config-set between Wetter/Kleidung/Both', __FILE__, __LINE__);
?>
<h1>
<?php echo Ajax::window('<a class="right" href="inc/plugin/window.wetter.php" title="Wetter-Diagramme anzeigen"><img src="img/mued.png" alt="Wetter-Diagramme anzeigen" /></a>'); ?>
	Wetter
</h1>

<small class="left">
	<?php echo Helper::Sport(MAINSPORT); ?>
</small>

<small class="right">
<?php
for ($x = START_YEAR; $x <= date("Y"); $x++) {
	echo $this->getInnerLink($x, 0, $x).' | ';
}
echo $this->getInnerLink('Gesamt', 0, -1);
?>
</small>


<?php
if ($this->year == -1) {
	$i = 0;
	$jahr = "Gesamt";
	$jstart = mktime(0,0,0,1,1,START_YEAR);
	$jende = time();	
} else {
	$i = $this->year;
	$jahr = $i;
	$jstart = mktime(0,0,0,1,1,$i);
	$jende = mktime(23,59,59,1,0,$i+1);
}
?>

	<br class="clear" />

<table cellspacing="0" width="100%" class="small">
	<tr class="b c">
		<td><?php echo ($this->year == -1) ? 'Gesamt' : $this->year; ?></td>
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
		<td colspan="13" />
	</tr>
	<tr class="a2 r">
		<td class="c">&#176;C</td>
<?php // Temperatur
$i = 1;
$temps = $mysql->fetch('SELECT
	AVG(`temperatur`) as `temp`,
	MONTH(FROM_UNIXTIME(`time`)) as `m`
FROM `ltb_training` WHERE
	`sportid`="'.MAINSPORT.'" AND
	`temperatur` IS NOT NULL
	'.($this->year != -1 ? 'AND YEAR(FROM_UNIXTIME(`time`))='.$this->year : '').'
GROUP BY MONTH(FROM_UNIXTIME(`time`))
ORDER BY `m` ASC
LIMIT 12', false, true);
if ($temps !== false) {
	foreach($temps as $temp) {
		// Fill empty columns
		for (; $i < $temp['m']; $i++)
			echo Helper::emptyTD();
		$i++;

		// Print data
		echo ('		<td>'.round($temp['temp']).' &deg;C</td>'.NL);
	}

	// Fill empty columns
	for (; $i < 12; $i++)
		echo Helper::emptyTD();
} else
	echo('		<td colspan="12" />'.NL);
?>
	</tr>





<?php // Wetterarten
$wetter_all = $mysql->fetch('SELECT `id` FROM `ltb_wetter` WHERE `name`!="unbekannt" ORDER BY `order` ASC');
if($wetter_all && mysql_num_rows($wetter_all))  ##
foreach($wetter_all as $w => $wetter) {
	echo('
	<tr class="a'.($w%2+1).' r">
		<td class="c">'.Helper::WetterImg($wetter['id']).'</td>');

	$i = 1;
	$data = $mysql->fetch('SELECT
		SUM(1) as `num`,
		MONTH(FROM_UNIXTIME(`time`)) as `m`
	FROM `ltb_training` WHERE
		`sportid`="'.MAINSPORT.'" AND
		`wetterid`='.$wetter['id'].'
		'.($this->year != -1 ? 'AND YEAR(FROM_UNIXTIME(`time`))='.$this->year : '').'
	GROUP BY MONTH(FROM_UNIXTIME(`time`))
	ORDER BY `m` ASC
	LIMIT 12', false, true);
	if ($data !== false) {
		foreach($data as $dat) {
			// Fill empty columns
			for (; $i < $dat['m']; $i++)
				echo Helper::emptyTD();
			$i++;
	
			// Print data
			echo ($dat['num'] != 0)
				? ('		<td>'.$dat['num'].'x</td>'.NL)
				: Helper::emptyTD();
		}
	
		// Fill empty columns
		for (; $i < 12; $i++)
			echo Helper::emptyTD();
	} else
		echo('		<td colspan="12" />'.NL);

	echo('
	</tr>');
}
?>
	<tr class="space">
		<td colspan="13" />
	</tr>





<?php // Kleidungsarten
$nums = $mysql->fetch('SELECT
	SUM(1) as `num`,
	MONTH(FROM_UNIXTIME(`time`)) as `m`
FROM `ltb_training` WHERE
	`sportid`="'.MAINSPORT.'" AND
	`kleidung`!=""
	'.($this->year != -1 ? 'AND YEAR(FROM_UNIXTIME(`time`))='.$this->year : '').'
GROUP BY MONTH(FROM_UNIXTIME(`time`))
ORDER BY `m` ASC
LIMIT 12', false, true);
if($nums && mysql_num_rows($nums)) {
foreach($nums as $dat)
	$num[$dat['m']] = $dat['num'];
} else {
$error->add('WARNING', 'Bisher keine Trainingsdaten eingetragen', __FILE__, 169);
}
$kleidungen = $mysql->fetch('SELECT `id`, `name` FROM `ltb_kleidung` ORDER BY `order` ASC');
if($kleidungen && mysql_num_rows($kleidungen)) {
foreach($kleidungen as $k => $kleidung) {
	echo('
	<tr class="a'.($k%2+1).' r">
		<td class="r">'.$kleidung['name'].'</td>');

	$i = 1;
	$data = $mysql->fetch('SELECT
		SUM(IF(FIND_IN_SET("'.$kleidung['id'].'", `kleidung`)!=0,1,0)) as `num`,
		MONTH(FROM_UNIXTIME(`time`)) as `m`
	FROM `ltb_training` WHERE
		`sportid`="'.MAINSPORT.'"
		'.($this->year != -1 ? 'AND YEAR(FROM_UNIXTIME(`time`))='.$this->year : '').'
	GROUP BY MONTH(FROM_UNIXTIME(`time`))
	HAVING `num`!=0
	ORDER BY `m` ASC
	LIMIT 12', false, true);
	if ($data !== false) {
		foreach($data as $dat) {
			// Fill empty columns
			for (; $i < $dat['m']; $i++)
				echo Helper::emptyTD();
			$i++;
	
			// Print data
			if ($dat['num'] != 0)
				echo('
		<td class="r">
			<span title="'.$dat['num'].'x">
				'.round($dat['num']*100/$num[$dat['m']]).' &#37;
			</span>
		</td>'.NL);
			else
				echo Helper::emptyTD();
		}
	
		// Fill empty columns
		for (; $i < 12; $i++)
			echo Helper::emptyTD();
	} else
		echo('		<td colspan="12" />'.NL);

	echo('
	</tr>');
}
} else {
$error->add('WARNING', 'Keine Kleidung eingetragen', __FILE__, 184); 
}
?>
	<tr class="space">
		<td colspan="13" />
	</tr>
	<tr>
		<td colspan="13">&nbsp;</td>
	</tr>
</table>
 
<table cellspacing="0" width="100%" class="small">
	<tr class="b c">
		<td />
		<td>Temperaturen</td>
		<td>&Oslash;</td>
		<td colspan="2" />
		<td>Temperaturen</td>
		<td>&Oslash;</td>
		<td colspan="2" />
		<td>Temperaturen</td>
		<td>&Oslash;</td>
	</tr>
	<tr class="space">
		<td colspan="11" />
	</tr>
	<tr class="a1 r">
<?php // Temperaturbereiche
$kleidungen = $mysql->fetch('SELECT * FROM `ltb_kleidung` ORDER BY `order` ASC');
if($kleidungen && mysql_num_rows($kleidungen)) {
foreach($kleidungen as $i => $kleidung) {
	if ($i%3 == 0):
?>
	</tr>
	<tr class="a<?php echo ($i%2+1);?> r">
<?php else: ?>
		<td>&nbsp;&nbsp;</td>
<?php
	endif;
	$dat = $mysql->fetch('SELECT
			AVG(`temperatur`) as `avg`,
			MAX(`temperatur`) as `max`,
			MIN(`temperatur`) as `min`
		FROM `ltb_training` WHERE `sportid`="'.MAINSPORT.'" AND
		`temperatur` IS NOT NULL AND
		FIND_IN_SET('.$kleidung['id'].',`kleidung`) != 0
		'.($this->year != -1 ? 'AND YEAR(FROM_UNIXTIME(`time`))='.$this->year : ''));
?>
		<td class="l"><?php echo($kleidung['name']); ?></td>
<?php if ($dat['min'] != ''): ?>
		<td><?php echo($dat['min']); ?>&deg;C bis <?php echo($dat['max']); ?>&deg;C</td>
		<td><?php echo round($dat['avg']); ?>&deg;C</td>
<?php else: ?>
		<td colspan="2" class="c"><em>-</em></td>
<?php endif;
}
} else {
	$error->add('WARNING', 'Keine Kleidung eingetragen', __FILE__, 256);
}

for (; $i%3 != 1; $i++):
?>
		<td colspan="3">&nbsp;</td>
<?php endfor; ?>
	</tr>
	<tr class="space">
		<td colspan="11" />
	</tr>
</table>