<?php
/**
 * This file contains the plugin "Trainingszeiten".
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Stat ($this)
 * @uses class::Mysql
 * @uses class::Helper
 *
 * Last modified 2010/09/03 21:01 by Hannes Christiansen
 */
/**
 * Plugin-installer, will be called by class::Plugin for installing this plugin.
 */
function stat_trainingszeiten_installer() {
	$type = 'stat';
	$filename = 'stat.trainingszeiten.inc.php';
	$name = 'Trainingszeiten';
	$description = 'Auflistung n�chtlicher Trainings und Diagramme �ber die Trainingszeiten.';
	// TODO Include the plugin-installer
}

$Mysql = Mysql::getInstance();
$Error = Error::getInstance();
?>
<h1>Trainingszeiten</h1>

<table cellspacing="0" width="98%" style="margin:0 5px 25px 5px;" class="left small">
	<tr class="b c">
		<td colspan="8">N&auml;chtliches Training</td>
	</tr>
<?php
$sports_not_short = '';
$sports = $Mysql->fetch('SELECT `id` FROM `ltb_sports` WHERE `short` = 0', false, true);
foreach($sports as $sport)
	$sports_not_short .= $sport['id'].',';

$nights = $Mysql->fetch('SELECT * FROM (
	SELECT *,
		HOUR(FROM_UNIXTIME(`time`)) as `H`,
		MINUTE(FROM_UNIXTIME(`time`)) as `MIN`
	FROM `ltb_training`
	WHERE
		`sportid` IN('.substr($sports_not_short,0,-1).') AND
		(HOUR(FROM_UNIXTIME(`time`))!=0 OR MINUTE(FROM_UNIXTIME(`time`))!=0)
	ORDER BY
		ABS(6-(`H`+3)%24-`MIN`/60) ASC,
		`MIN` DESC LIMIT 20
	) t
ORDER BY
	(`H`+12)%24 ASC,
	`MIN` ASC');

foreach($nights as $i => $night) {
	$sport = Helper::Sport($night['sportid'],true);
	if ($i%2 == 0)
		echo('<tr class="a'.(round($i/2)%2+1).'">');
	echo('
		<td class="b">'.date("H:i", $night['time']).' Uhr</td>
		<td>'.Ajax::trainingLink($night['id'], Icon::getSportIcon($night['sportid'])).'</td>
		<td>'.($night['distanz'] != 0 ? Helper::Km($night['distanz']) : Helper::Time($night['dauer']).' '.$sport['name']).'</td>
		<td>'.DataBrowser::getLink(date("d.m.Y",$night['time']), Helper::Weekstart($night['time']), Helper::Weekend($night['time'])).'</td>');
	if ($i%2 == 1)
		echo('</tr>');
}
?>
</table>

<?php $Error->addTodo('Use Class::Draw as soon as possible', __FILE__, __LINE__); ?>
<img class="right" src="lib/draw/trainingstage.php" />
<img class="left" src="lib/draw/trainingszeiten.php" />

<br class="clear" />
&nbsp;