<?php
/**
 * This file contains the plugin "Rekorde".
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Stat ($this)
 * @uses class::Mysql
 * @uses class::Helper
 *
 * Last modified 2011/01/08 18:42 by Michael Pohl
 */
/**
 * Plugin-installer, will be called by class::Plugin for installing this plugin.
 */
function stat_rekorde_installer() {
	$type = 'stat';
	$filename = 'stat.rekorde.inc.php';
	$name = 'Rekorde';
	$description = 'Am schnellsten, am längsten, am weitesten: Die Rekorde aus dem Training.';
	// TODO Include the plugin-installer
}

$Mysql = Mysql::getInstance();
$Error = Error::getInstance();
?>
<h1>Rekorde</h1>


<?php
$rekorde = array();
$rekorde[] = array('name' => 'Schnellsten Trainings',
	'sportquery' => 'SELECT * FROM `ltb_sports` WHERE `distanztyp`=1 ORDER BY `id` ASC',
	'datquery' => 'SELECT `id`, `time`, `dauer`, `distanz`, `sportid` FROM `ltb_training` WHERE `sportid`=\'.$sport[\'id\'].\' ORDER BY `pace` ASC, `dauer` DESC LIMIT 10',
	'eval' => '0');
$rekorde[] = array('name' => 'L&auml;ngsten Trainings',
	'sportquery' => 'SELECT * FROM `ltb_sports` ORDER BY `id` ASC',
	'datquery' => 'SELECT * FROM `ltb_training` WHERE `sportid`=\'.$sport[\'id\'].\' ORDER BY `distanz` DESC, `dauer` DESC LIMIT 10',
	'eval' => '1');

foreach ($rekorde as $rekord):
?>
<table cellspacing="0" width="100%" class="small">
	<tr class="b">
		<td colspan="11">
			<?php echo $rekord['name']; ?>
		</td>
	</tr>
	<tr class="space">
		<td colspan="11">
		</td>
	</tr>
<?php
	$Error->add('TODO', 'Set correct onclick-Code', __FILE__, __LINE__);
	eval('$sports = $Mysql->fetch(\''.$rekord['sportquery'].'\', false, true);');
	foreach ($sports as $i => $sport) {
		echo('
	<tr class="a'.($i%2 + 1).' r">
		<td class="b l">
			<img src="img/sports/'.$sport['bild'].'" /> '.$sport['name'].'
		</td>');
		eval('$data = $Mysql->fetch(\''.$rekord['datquery'].'\', false, true);');
		if (count($data) > 0) {
			foreach ($data as $j => $dat) {
				if ($rekord['eval'] == 0)
					$code = Helper::Tempo($dat['distanz'],$dat['dauer'],$sport['id'],false);
				elseif ($rekord['eval'] == 1)
					$code = ($dat['distanz'] != 0 ? Helper::Km($dat['distanz']) : Helper::Time($dat['dauer']));
				// TODO Set correct onclick-Code
				echo('
		<td>
			<span title="'.date("d.m.Y",$dat['time']).'">
				'.Ajax::trainingLink($dat['id'], $code).'
			</span>
		</td>');
			}
		} else {
			$Error->add('WARNING', 'Keine Trainingsdaten vorhanden', __FILE__, __LINE__);
		}
		for (; $j < 10; $j++) { echo('
		<td>
			&nbsp;
		</td>'); }
		echo('
	</tr>');
	} 
?>
	<tr class="space">
		<td colspan="11">
		</td>
	</tr>
</table>
<?php
endforeach;
?>

<table cellspacing="0" width="100%" class="small">
	<tr class="b">
		<td colspan="11">
			Trainingsreichsten Laufphasen
		</td>
	</tr>
	<tr class="space">
		<td colspan="11">
		</td>
	</tr>
	<tr class="a1 r">
		<td class="c b">
			Jahre
		</td>
<?php
// Jahre
$years = $Mysql->fetch('SELECT `sportid`, SUM(`distanz`) as `km`, YEAR(FROM_UNIXTIME(`time`)) as `year` FROM `ltb_training` WHERE `sportid`=1 GROUP BY `year` ORDER BY `km` DESC LIMIT 10', false, true);
if (count($years) > 0) {
	foreach ($years as $i => $year) {
		$link = 'daten(\''.mktime(0,0,0,1,1,$year['year']).'\',\''.mktime(0,0,0,1,1,$year['year']).'\',\''.mktime(23,59,50,12,31,$year['year']).'\');';
		echo('
			<td>
				<span class="link" title="'.$year['year'].'" onclick="'.$link.'">
					'.Helper::Km($year['km']).'
				</span>
			</td>');
	}
} else {
	$Error->add('WARNING', 'Keine Trainingsdaten vorhanden', __FILE__, __LINE__);
}
for (; $i < 10; $i++) { echo('
		<td>&nbsp;</td>'); }
?>
	</tr>

	<tr class="a2 r">
		<td class="c b">
			Monate
		</td>
<?php
// Monate
$months = $Mysql->fetch('SELECT `sportid`, SUM(`distanz`) as `km`, YEAR(FROM_UNIXTIME(`time`)) as `year`, MONTH(FROM_UNIXTIME(`time`)) as `month`, (MONTH(FROM_UNIXTIME(`time`))+100*YEAR(FROM_UNIXTIME(`time`))) as `monthyear` FROM `ltb_training` WHERE `sportid`=1 GROUP BY `monthyear` ORDER BY `km` DESC LIMIT 10', false, true);
if (count($months) > 0) {
	foreach ($months as $i => $month) {
		$link = 'daten(\''.mktime(0,0,0,$month['month'],1,$month['year']).'\',\''.mktime(0,0,0,$month['month'],1,$month['year']).'\',\''.mktime(23,59,50,$month['month']+1,0,$month['year']).'\');';
		echo('
			<td>
				<span class="link" title="'.Helper::Monat($month['month']).' '.$month['year'].'" onclick="'.$link.'">
					'.Helper::Km($month['km']).'
				</span>
			</td>');
	}
} else {
	$Error->add('WARNING', 'Keine Trainingsdaten vorhanden', __FILE__, __LINE__);
}
for ($i; $i < 10; $i++) { echo('
		<td>
		</td>'); }
?>
	</tr>

	<tr class="a1 r">
		<td class="c b">
			Wochen
		</td>
<?php
// Wochen
$weeks = $Mysql->fetch('SELECT `sportid`, SUM(`distanz`) as `km`, WEEK(FROM_UNIXTIME(`time`),1) as `week`, YEAR(FROM_UNIXTIME(`time`)) as `year`, YEARWEEK(FROM_UNIXTIME(`time`),1) as `weekyear`, `time` FROM `ltb_training` WHERE `sportid`=1 GROUP BY `weekyear` ORDER BY `km` DESC LIMIT 10', false, true);
if (count($weeks) > 0) {
	foreach ($weeks as $i => $week) {
		$link = 'daten(\''.$week['time'].'\',\''.Helper::Wochenstart($woche['time']).'\',\''.Helper::Wochenende($week['time']).'\');';
		echo('
			<td>
				<span class="link" title="KW '.$week['week'].' '.$week['year'].'" onclick="'.$link.'">
					'.Helper::Km($week['km']).'
				</span>
			</td>');
	}
} else {
	$Error->add('WARNING', 'Keine Trainingsdaten vorhanden', __FILE__, __LINE__);
}
for (; $i < 10; $i++) { echo('
		<td>
		</td>'); }
?>
	</tr>
	<tr class="space">
		<td colspan="11">
		</td>
	</tr>
</table>