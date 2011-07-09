<?php
/**
 * This file contains the plugin "Trainingspartner".
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Stat ($this)
 * @uses class::Mysql
 * @uses class::Error
 *
 * Last modified 2010/09/04 21:07 by Hannes Christiansen
 */
/**
 * Plugin-installer, will be called by class::Plugin for installing this plugin.
 */
function stat_trainingspartner_installer() {
	$type = 'stat';
	$filename = 'stat.trainingspartner.inc.php';
	$name = 'Trainingspartner';
	$description = 'Wie oft hast du mit wem gemeinsam trainiert?';
	// TODO Include the plugin-installer
}

$Mysql = Mysql::getInstance();
?>
<h1>Trainingspartner</h1>

<table style="width:95%;" style="margin:0 5px;" class="small">
	<tr class="b c">
		<td colspan="2">Alle Trainingspartner</td>
	</tr>
	<tr class="space">
		<td colspan="2" />
	</tr>
<?php
$partner = array();
$trainings = $Mysql->fetchAsArray('SELECT `trainingspartner` FROM `ltb_training` WHERE `trainingspartner` != ""');
if (count($trainings) == 0)
	echo('
	<tr class="a1">
		<td class="b">0x</td>
		<td><em>Du hast bisher nur alleine trainiert.</em></td>
	</tr>');
else {
	foreach($trainings as $training) {
		$trainingspartner = explode(', ', $training['trainingspartner']);
		foreach($trainingspartner as $name) {
			if (!isset($partner[$name]))
				$partner[$name] = 1;
			else
				$partner[$name]++;
		}
	}

	$row_num = INFINITY;
	$i = 0;
	array_multisort($partner, SORT_DESC);

	foreach ($partner as $name => $name_num) {
		if ($row_num == $name_num)
			echo(', ');
		else {
			if ($name_num != 1 && $row_num != INFINITY)
				echo('
			</td>
		</tr>');

			$row_num = $name_num;
			$i++;
			echo('
		<tr class="a'.($i%2+1).'">
			<td class="b">'.$row_num.'x</td>
			<td>');
		}

		echo DataBrowser::getSearchLink($name, 'opt[trainingspartner]=like&val[trainingspartner]='.$name);
	}
	echo('
			</td>
		</tr>');
}
?>
</table>