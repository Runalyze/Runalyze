<?php
/**
 * This file contains the plugin "Wettkaempfe".
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Stat ($this)
 * @uses class::Mysql
 * @uses class::Error
 * @uses class::Ajax
 * @uses class::Helper
 * @uses CONFIG_USE_...
 * @uses WK_TYPID
 *
 * Last modified 2011/01/08 18:42 by Michael Pohl
 */
/**
 * Plugin-installer, will be called by class::Plugin for installing this plugin.
 */
function stat_wettkampf_installer() {
	$type = 'stat';
	$filename = 'stat.wettkampf.inc.php';
	$name = 'Wettk&auml;mpfe';
	$description = 'Bestzeiten und alles weitere zu den bisher gelaufenen Wettk&auml;mpfen.';
	// TODO Include the plugin-installer
}

$Mysql = Mysql::getInstance();
$Error = Error::getInstance();

$Error->addTodo('Add statistics about the weather', __FILE__, __LINE__);
?>
<h1>Wettk&auml;mpfe</h1>

<small class="right">
	<a class="change" href="#alle" target="tab_content" >Alle Wettk&auml;mpfe</a> |
	<a class="change" href="#last_wks" target="tab_content">Letzten Wettk&auml;mpfe</a> |
	<a class="change" href="#bestzeiten" target="tab_content" name="bestzeit-dia">Bestzeiten</a>
</small>

<br class="clear" />

<?php
function show_table_start() {
	echo('
<table cellspacing="0" width="100%">
	<tr class="b c">
		<td>Datum</td>
		<td>Lauf</td>
		<td>Distanz</td>
		<td>Zeit</td>
		<td>Pace</td>'.(CONFIG_USE_PULS ? '
		<td>Puls</td>' : '').''.(CONFIG_USE_WETTER ? '
		<td>Wetter</td>' : '').'
	</tr>  
	<tr class="space">
		<td colspan="7" />
	</tr>');
}

function show_wk_tr($wk, $i) {
	echo('
	<tr class="a'.($i%2 + 1).' r">
		<td class="c small">'.DataBrowser::getLink(date("d.m.Y", $wk['time']), Helper::Weekstart($wk['time']), Helper::Weekend($wk['time'])).'</a></td>
		<td class="l"><strong>'.Ajax::trainingLink($wk['id'], $wk['bemerkung']).'</strong></td>
		<td>'.Helper::Km($wk['distanz'], (round($wk['distanz']) != $wk['distanz'] ? 1 : 0), $wk['bahn']).'</td>
		<td>'.Helper::Time($wk['dauer']).'</td>
		<td class="small">'.$wk['pace'].'/km</td>'.(CONFIG_USE_PULS ? '
		<td class="small">'.Helper::Unknown($wk['puls']).' / '.Helper::Unknown($wk['puls_max']).' bpm</td>' : '').''.(CONFIG_USE_WETTER ? '
		<td class="small">'.($wk['temperatur'] != 0 && $wk['wetterid'] != 0 ? $wk['temperatur'].' &deg;C '.Helper::WeatherImage($wk['wetterid']) : '').'</td>' : '').'
	</tr>');	
}

function show_empty_tr($i, $text = '') {
	echo('
	<tr class="a'.($i%2 + 1).'">
		<td colspan="7">'.$text.'</td>
	</tr>');
}

function show_table_end() {
	echo('
	<tr class="space">
		<td colspan="7" />
	</tr>
</table>');
}
?>

<?php // ALLE WETTKAEMPFE ?>
<div id="alle" class="change" style="display:none;">
<?php
show_table_start();

$wks = $Mysql->fetchAsArray('SELECT * FROM `ltb_training` WHERE `typid`='.WK_TYPID.' ORDER BY `time` DESC');
foreach($wks as $i => $wk)
	show_wk_tr($wk, $i);

show_table_end();
?>
</div>

<?php // LETZTEN WETTKAEMPFE ?>
<div id="last_wks" class="change" style="display:block;">
<?php
show_table_start();

$wks = $Mysql->fetchAsArray('SELECT * FROM `ltb_training` WHERE `typid`='.WK_TYPID.' ORDER BY `time` DESC LIMIT '.$this->config['last_wk_num']['var']);
if (count($wks) > 0) {
	foreach($wks as $i => $wk)
		show_wk_tr($wk, $i);
} else {
	show_empty_tr(1, 'Keine Wettk&auml;mpfe gefunden.');
	$Error->addWarning('Keine Trainingsdaten vorhanden', __FILE__, 100);
}
show_table_end();
?>
</div>

<?php // BESTZEITEN ?>
<div id="bestzeiten" class="change" style="display:none;">
<?php
show_table_start();

$distances = array();
$dists = $Mysql->fetchAsArray('SELECT `distanz`, SUM(1) as `wks` FROM `ltb_training` WHERE `typid`='.WK_TYPID.' GROUP BY `distanz`');
foreach($dists as $i => $dist) {
	if ($dist['wks'] > 1) {
		$distances[] = $dist['distanz'];

		$wk = $Mysql->fetchSingle('SELECT * FROM `ltb_training` WHERE `typid`='.WK_TYPID.' AND `distanz`='.$dist['distanz'].' ORDER BY `dauer` ASC');
		show_wk_tr($wk, $i);
	}
}

show_table_end();
?>

	<small style="text-align:center;display:block;">
<?php
$first = true;
foreach ($distances as $km) {
	echo('
		'.(!$first ? '| ' : '').Ajax::imgChange('<a href="inc/draw/plugin.wettkampf.php?km='.$km.'">'.Helper::Km($km, (round($km) != $km ? 1 : 0), ($km <= 3)).'</a>','bestzeit-diagramm'));
	$first = false;
}

$display_km = $distances[0];
if (in_array($this->config['main_distance']['var'], $distances))
	$display_km = $this->config['main_distance']['var'];
?>
	</small>

<?php if (count($distances) > 0): ?>
	<div class="bigImg" style="height:190px;width:480px; margin:0 auto;">
		<img id="bestzeit-diagramm" src="inc/draw/plugin.wettkampf.php?km=<?php echo $display_km; ?>" width="480" height="190" />
	</div>
<?php endif; ?>


	<table style="width:100%;">
		<tr class="b c">
			<td></td>
<?php
$year = array();
$dists = array();
$kms = (is_array($this->config['pb_distances']['var'])) ? $this->config['pb_distances']['var'] : array(3, 5, 10, 21.1, 42.2);
foreach ($kms as $km)
	$dists[$km] = array('sum' => 0, 'pb' => INFINITY);

$wks = $Mysql->fetchAsArray('SELECT YEAR(FROM_UNIXTIME(`time`)) as `y`, `distanz`, `dauer` FROM `ltb_training` WHERE `typid`='.WK_TYPID.' ORDER BY `y` ASC');
foreach ($wks as $wk) {
	if (!isset($year[$wk['y']])) {
		$year[$wk['y']] = $dists;
		$year['sum'] = 0;
	}
	$year[$wk['y']]['sum']++;
	foreach($kms as $km)
		if ($km == $wk['distanz']) {
			$year[$wk['y']][$km]['sum']++;
			if ($wk['dauer'] < $year[$wk['y']][$km]['pb'])
				$year[$wk['y']][$km]['pb'] = $wk['dauer'];
		}
}
		
foreach ($year as $y => $y_dat)
	if ($y != 'sum')
		echo('
			<td>'.$y.'</td>');
?>
		</tr>
		<tr class="space">
			<td colspan="<?php echo sizeof($year); ?>" />
		</tr>
<?php
foreach ($kms as $i => $km) {
	echo('
		<tr class="a'.($i%2+1).' r">
			<td class="b">'.Helper::Km($km, 1, $km <= 3).'</td>');

	foreach ($year as $key => $y)
		if ($key != 'sum')
			echo('
			<td>'.($y[$km]['sum'] != 0 ? '<small>'.Helper::Time($y[$km]['pb']).'</small> '.$y[$km]['sum'].'x' : '&nbsp;').'</td>');

	echo('
		</tr>');
}
?>
		<tr class="space">
			<td colspan="<?php echo sizeof($year); ?>" />
		</tr>
		<tr class="a<?php echo (($i+1)%2+1) ?> r">
			<td class="b">Gesamt</td>
<?php
foreach ($year as $i => $y)
	if ($i != 'sum')
		echo('
			<td>'.$y['sum'].'x</td>');
?>
		</tr>
	</table>
</div>