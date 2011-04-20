<?php
require('inc/class.Frontend.php');

$Frontend = new Frontend(false, __FILE__);
$Frontend->displayHeader();

$Error = Error::getInstance();
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
 */
$Error->addTodo('Plugins have to be deacitvated automatically if modus is unused',__FILE__,__LINE__);
$stats = Mysql::getInstance()->fetchAsArray('SELECT * FROM `ltb_plugin` WHERE `type`="stat" AND `active`=1 ORDER BY `order` ASC');
foreach($stats as $i => $stat) {
	$Stat = new Stat($stat['id']);
	if ($i == 0)
		$Stat_active = $Stat;
	echo('
		<li'.(($i == 0) ? ' class="active"' : '').'>'.$Stat->getLink().'</li>');
}

$other = Mysql::getInstance()->fetch('SELECT `id` FROM `ltb_plugin` WHERE `type`="stat" AND `active`=2 ORDER BY `order` ASC LIMIT 1');
if ($other !== false) {
	$Stat = new Stat($other['id']);
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
if ($Stat_active instanceof Stat)
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