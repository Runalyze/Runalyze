<?php
/**
 * This file contains the plugin "Schuhe".
 * It displays all shoes with their max, average and total kilometers and pace.
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Stat ($this)
 * @uses class::Mysql ($mysql)
 * @uses lib/draw/schuhbalken.php
 *
 * Last modified 2010/08/09 07:53 by Hannes Christiansen
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
?>
<h1>Schuhe</h1>
 
<table cellspacing="0" width="100%">
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
$error->add('TODO', 'Set correct onclick-link', __FILE__, __LINE__);
$schuhe = $mysql->fetch('SELECT * FROM `ltb_schuhe` ORDER BY `inuse` DESC, `km` DESC');
foreach($schuhe as $i => $schuh) {
	$i++;
	$training_dist = $mysql->fetch('SELECT * FROM `ltb_training` WHERE `schuhid`='.$schuh['id'].' ORDER BY `distanz` DESC LIMIT 1');
	$training_pace = $mysql->fetch('SELECT * FROM `ltb_training` WHERE `schuhid`='.$schuh['id'].' ORDER BY `pace` ASC LIMIT 1');
	$trainings = $mysql->num('SELECT * FROM `ltb_training` WHERE `schuhid`="'.$schuh['id'].'"');
	$in_use = $schuh['inuse']==1 ? '' : ' small';
	echo('
	<tr class="a'.($i%2 + 1).' r" style="background:url(lib/draw/schuhbalken.php?km='.round($schuh['km']).') no-repeat bottom left;">
		<td class="small">'.$trainings.'x</td>
		<td class="b'.$in_use.' l"><span class="link" onclick="submit_suche(\'dat[0]=schuhid&opt[0]=is&val[0]='.$schuh['id'].'\')">'.$schuh['name'].'</span></td>
		<td class="small">'.$schuh['kaufdatum'].'</td>
		<td>'.(($trainings != 0) ? Helper::Km($schuh['km']/$trainings) : '-').'</td>
		<td>'.(($trainings != 0) ? Helper::Tempo($schuh['km'], $schuh['dauer']) : '-').'</td>
		<td class="small"><span class="link" onClick="seite(\'training\',\''.$training_dist['id'].'\')">'.Helper::Km($training_dist['distanz']).'</span></td>
		<td class="small"><span class="link" onClick="seite(\'training\',\''.$training_pace['id'].'\')">'.$training_pace['pace'].'/km</span></td>
		<td>'.Helper::Time($schuh['dauer']).'</td>
		<td>'.Helper::Km($schuh['km']).'</td>
	</tr>');
}
echo('
	<tr class="space"><td colspan="9" /></tr>'); 
?>
</table>