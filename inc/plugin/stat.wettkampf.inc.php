<?php
/**
 * This file contains the plugin "Wettkämpfe".
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Stat ($this)
 * @uses class::Mysql ($mysql)
 * @uses class::Ajax
 * @uses class::Helper
 * @uses CONFIG_USE_...
 * @uses WK_TYPID
 *
 * Last modified 2010/08/30 20:42 by Hannes Christiansen
 */
/**
 * Plugin-installer, will be called by class::Plugin for installing this plugin.
 */
function stat_wettkampf_installer() {
	$type = 'stat';
	$filename = 'stat.wettkampf.inc.php';
	$name = 'Wettkämpfe';
	$description = 'Bestzeiten und alles weitere zu den bisher gelaufenen Wettkämpfen.';
	// TODO Include the plugin-installer
}

$error->add('TODO','Add statistics about the weather',__FILE__,__LINE__);
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

$error->add('TODO', 'Set correct onclick-link for date-link', __FILE__, __LINE__);
function show_wk_tr($wk, $i) {
	echo('
	<tr class="a'.($i%2 + 1).' r">
		<td class="c small"><a href="#" onclick="daten(\''.$wk['time'].'\',\''.Helper::Wochenstart($wk['time']).'\',\''.Helper::Wochenende($wk['time']).'\')">'.date("d.m.Y", $wk['time']).'</a></td>
		<td class="l"><strong>'.Ajax::trainingLink($wk['id'],$wk['bemerkung']).'</strong></td>
		<td>'.Helper::Km($wk['distanz'], (round($wk['distanz']) != $wk['distanz'] ? 1 : 0), $wk['bahn']).'</td>
		<td>'.Helper::Time($wk['dauer']).'</td>
		<td class="small">'.$wk['pace'].'/km</td>'.(CONFIG_USE_PULS ? '
		<td class="small">'.Helper::Unbekannt($wk['puls']).' / '.Helper::Unbekannt($wk['puls_max']).' bpm</td>' : '').''.(CONFIG_USE_WETTER ? '
		<td class="small">'.($wk['temperatur'] != 0 && $wk['wetterid'] != 0 ? $wk['temperatur'].' &deg;C '.Helper::WetterImg($wk['wetterid']) : '').'</td>' : '').'
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

$wks = $mysql->fetch('SELECT * FROM `ltb_training` WHERE `typid`='.WK_TYPID.' ORDER BY `time` DESC');
foreach($wks as $i => $wk)
	show_wk_tr($wk, $i);

show_table_end();
?>
</div>

<?php // LETZTEN WETTKAEMPFE ?>
<div id="last_wks" class="change" style="display:block;">
<?php
show_table_start();

$error->add('TODO','Last WKs: Set LAST_WK_NUM as config-var',__FILE__,__LINE__);
define('LAST_WK_NUM',10);
$wks = $mysql->fetch('SELECT * FROM `ltb_training` WHERE `typid`='.WK_TYPID.' ORDER BY `time` DESC LIMIT '.LAST_WK_NUM);
foreach($wks as $i => $wk)
	show_wk_tr($wk, $i);

show_table_end();
?>
</div>

<?php // BESTZEITEN ?>
<div id="bestzeiten" class="change" style="display:none;">
<?php
show_table_start();

$distances = array();
$dists = $mysql->fetch('SELECT `distanz`, SUM(1) as `wks` FROM `ltb_training` WHERE `typid`='.WK_TYPID.' GROUP BY `distanz`');
foreach($dists as $i => $dist) {
	if ($dist['wks'] > 1) {
		$distances[] = $dist['distanz'];

		$wk = $mysql->fetch('SELECT * FROM `ltb_training` WHERE `typid`='.WK_TYPID.' AND `distanz`='.$dist['distanz'].' ORDER BY `dauer` ASC LIMIT 1');
		show_wk_tr($wk, $i);
	}
}

show_table_end();
?>

	<small style="text-align:center;display:block;">
<?php
$error->add('TODO', 'Set link with Class::Ajax', __FILE__, __LINE__);
// TODO Set link with AJAX-Class
$first = true;
foreach($distances as $km) {
	echo('
		'.(!$first ? '|' : '').' <a href="#bestzeit-dia" onclick="document.getElementById(\'bestzeit-diagramm\').src=\'lib/draw/bestzeit.php?km='.$km.'\';">'.km($km, (round($km) != $km ? 1 : 0)).'</a>');
	$first = false;
}
?>
	</small>

	<center>
		<img id="bestzeit-diagramm" src="lib/draw/bestzeit.php?km=10" width="482" height="192" />
	</center>


	<table cellspacing="0" width="100%">
		<tr class="b c">
			<td></td>
<?php
$year = array();
$dists = array();
$kms = array(3, 5, 10, 21.1, 42.2);
foreach($kms as $km)
	$dists[$km] = array('sum' => 0, 'pb' => INFINITY);

$wks = $mysql->fetch('SELECT YEAR(FROM_UNIXTIME(`time`)) as `y`, `distanz`, `dauer` FROM `ltb_training` WHERE `typid`='.WK_TYPID.' ORDER BY `y` ASC');
foreach($wks as $wk) {
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
		
foreach($year as $y => $y_dat)
	if ($y != 'sum')
		echo('
			<td>'.$y.'</td>');
?>
		</tr>
		<tr class="space">
			<td colspan="<?php echo sizeof($year); ?>" />
		</tr>
<?php
foreach($kms as $i => $km) {
	echo('
		<tr class="a'.($i%2+1).' r">
			<td class="b">'.Helper::Km($km).'</td>');

	foreach($year as $key => $y)
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