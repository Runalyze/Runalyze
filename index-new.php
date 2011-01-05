<?php
require('inc/class.Frontend.php');
$Frontend = new Frontend(false, __FILE__);

if ($_GET['action'] == 'do')
	include('config/mysql_query.php');

$Frontend->displayHeader();
?>


<div id="r">
<?php
$panels = $mysql->fetch('SELECT * FROM `ltb_plugin` WHERE `type`="panel" AND `active`>0 ORDER BY `order` ASC');
foreach($panels as $i => $panel) {
	$panel = new Panel($panel['id']);
	$panel->display();
}
?>
</div>

<div id="l">
	<div id="daten">
<?php $error->add('TODO','class::DataBrowser has to be included',__FILE__,__LINE__); ?>
		<div id="daten_results" class="panel"></div>
	</div>

	<ul class="tabs">
		<li id="tabs_back"><img src="img/arrBack.png" /></li>
<?php
/**
 * STATISTIC PLUGINS
 */
$error->add('TODO','Plugins have to be deacitvated automatically if modus is unused',__FILE__,__LINE__);
$stats = $mysql->fetch('SELECT * FROM `ltb_plugin` WHERE `type`="stat" AND `active`=1 ORDER BY `order` ASC');
foreach($stats as $i => $stat) {
	$stat = new Stat($stat['id']);
	if ($i == 0)
		$stat_active = $stat;
	echo('
		<li'.(($i == 0) ? ' class="active"' : '').'>'.$stat->getLink().'</li>');
}

$other = $mysql->fetch('SELECT `id` FROM `ltb_plugin` WHERE `type`="stat" AND `active`=2 ORDER BY `order` ASC LIMIT 1');
if ($other !== false) {
	$stat = new Stat($other['id']);
	echo('
		<li>'.$stat->getLink().'</li>');
}
?>
	</ul>
	<div id="statistiken" class="panel tabs">
		<div id="tab_content_prev">
			<em>Es wurde zuvor nichts geladen.</em>
		</div>
		<div id="tab_content">
<?php $stat_active->display(); ?>
		</div>
	</div>


</div>

<?php
$Frontend->displayFooter();
$Frontend->close();
?>