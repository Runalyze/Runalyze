<?php
/**
 * This file contains the plugin "Schuhe".
 * It displays all shoes with their max, average and total kilometers and pace.
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Stat ($this)
 * @uses class::Mysql
 * @uses lib/draw/schuhbalken.php
 *
 * Last modified 2011/01/08 18:42 by Michael Pohl
 */
/**
 * Plugin-installer, will be called by class::Plugin for installing this plugin.
 */
function stat_schuhe_installer() {
	$type = 'stat';
	$filename = 'stat.schuhe.inc.php';
	$name = 'Schuhe';
	$description = 'Ausf&uuml;hrliche Statistiken zu den Schuhen: Durchschnittliche, maximale und absolute Leistung (Kilometer / Tempo).';
	// TODO Include the plugin-installer
}

$Mysql = Mysql::getInstance();
$Error = Error::getInstance();
?>
<h1>Schuhe</h1>
 
<table style="width:100%;">
	<tr class="b c">
		<td colspan="2" />
		<td class="small">Kaufdatum</td>
		<td>&Oslash; km</td>
		<td>&Oslash; Pace</td>
		<td class="small" colspan="2">max.</td>
		<td>Dauer</td>
		<td>Distanz</td>
	</tr>
	<tr class="space">
		<td colspan="9" />
	</tr>
<?php
$schuhe = $Mysql->fetchAsArray('SELECT * FROM `ltb_schuhe` ORDER BY `inuse` DESC, `km` DESC');
if (count($schuhe) > 0) {
	foreach($schuhe as $i => $schuh) {
		$i++;
		$training_dist = $Mysql->fetchSingle('SELECT * FROM `ltb_training` WHERE `schuhid`='.$schuh['id'].' ORDER BY `distanz` DESC');
		$training_pace = $Mysql->fetchSingle('SELECT * FROM `ltb_training` WHERE `schuhid`='.$schuh['id'].' ORDER BY `pace` ASC');
		$trainings = $Mysql->num('SELECT * FROM `ltb_training` WHERE `schuhid`="'.$schuh['id'].'"');
		$in_use = $schuh['inuse']==1 ? '' : ' small';
		echo('
		<tr class="a'.($i%2 + 1).' r" style="background:url(inc/draw/plugin.schuhe.php?km='.round($schuh['km']).') no-repeat bottom left;">
			<td class="small">'.$trainings.'x</td>
			<td class="b'.$in_use.' l">'.DataBrowser::getSearchLink($schuh['name'], 'opt[schuhid]=is&val[schuhid][0]='.$schuh['id']).'</td>
			<td class="small">'.$schuh['kaufdatum'].'</td>
			<td>'.(($trainings != 0) ? Helper::Km($schuh['km']/$trainings) : '-').'</td>
			<td>'.(($trainings != 0) ? Helper::Speed($schuh['km'], $schuh['dauer']) : '-').'</td>
			<td class="small">'.Ajax::trainingLink($training_dist['id'], Helper::Km($training_dist['distanz'])).'</td>
			<td class="small">'.Ajax::trainingLink($training_pace['id'], $training_pace['pace'].'/km').'</td>
			<td>'.Helper::Time($schuh['dauer']).'</td>
			<td>'.Helper::Km($schuh['km']).'</td>
		</tr>');
	}
} else {
	echo('<tr class="a1"><td colspan="9">Keine Schuhe vorhanden.</td></tr>');
	$Error->addWarning('Bisher keine Schuhe eingetragen', __FILE__, __LINE__);
}
echo('
	<tr class="space"><td colspan="9" /></tr>'); 
?>
</table>