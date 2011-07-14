<?php
/**
 * Runalyze - Running analytics
 * 
 * This main file loads the frontend class and controls the output.
 */
require('inc/class.Frontend.php');

$Frontend = new Frontend(false, __FILE__);
$Frontend->displayHeader();
?>


<div id="r">
<?php $Frontend->displayPanels(); ?>
</div>

<div id="l">
	<div id="dataPanel" class="panel">
		<div id="daten">
<?php
$DataBrowser = new DataBrowser();
$DataBrowser->display();
?>
		</div>
	</div>

	<ul class="tabs">
		<li id="tabs_back"><img src="img/arrBack.png" /></li>
<?php
/**
 * STATISTIC PLUGINS
 * @TODO Move to class::Frontend
 */
Error::getInstance()->addTodo('Plugins have to be deacitvated automatically if modus is unused', __FILE__, __LINE__);
$stats = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'plugin` WHERE `type`="stat" AND `active`=1 ORDER BY `order` ASC');
foreach($stats as $i => $stat) {
	//$Stat = new Stat($stat['id']);
	$Stat = Plugin::getInstanceFor($stat['key']);
	if ($i == 0)
		$Stat_active = $Stat;
	echo('
		<li'.(($i == 0) ? ' class="active"' : '').'>'.$Stat->getLink().'</li>');
}

$other = Mysql::getInstance()->fetchSingle('SELECT `key` FROM `'.PREFIX.'plugin` WHERE `type`="stat" AND `active`=2 ORDER BY `order` ASC');
if ($other !== false) {
	//$Stat = new Stat($other['id']);
	$Stat = Plugin::getInstanceFor($other['key']);
	echo('
		<li>'.$Stat->getLink().'</li>');
}
?>
	</ul>
	<div id="statistiken" class="panel tabs">
		<div id="tab_content_prev">
			<em>Es wurde zuvor nichts geladen.</em>
		</div>
		<div id="tab_content">
<?php
if ($Stat_active instanceof Plugin) //Stat)
	$Stat_active->display();
else
	echo('<em>Es sind keine Statistiken vorhanden. Du musst sie in der Konfiguration aktivieren.</em>');
?>
		</div>
	</div>


</div>

<?php
$Frontend->displayFooter();
$Frontend->close();
?>