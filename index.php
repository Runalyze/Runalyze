<?php
/**
 * RUNALYZE
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @copyright http://www.runalyze.de/
 * 
 * This main file loads the frontend class and controls the output.
 */
if (!file_exists('config.php')) {
	include 'install.php';
	exit();
}

require 'inc/class.Frontend.php';

$Frontend = new Frontend(false, __FILE__);
$Frontend->displayHeader();
?>

<div id="panels">
	<?php $Frontend->displayPanels(); ?>
</div>

<div id="main">
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
		$Stats = Plugin::getKeysAsArray(Plugin::$STAT, Plugin::$ACTIVE);
		foreach ($Stats as $i => $key)
			echo '<li'.($i == 0 ? ' class="active"' : '').'>'.Plugin::getInstanceFor($key)->getLink().'</li>'.NL;
		
		if (PluginStat::hasVariousStats())
			echo '<li>'.PluginStat::getLinkForVariousStats().'</li>';
		?>
	</ul>
	<div id="statistiken" class="panel tabs">
		<div id="tab_content_prev">
			<em>Es wurde zuvor nichts geladen.</em>
		</div>

		<div id="tab_content">
			<?php
			if (empty($Stats))
				echo('<em>Es sind keine Statistiken vorhanden. Du musst sie in der Konfiguration aktivieren.</em>');
			else
				Plugin::getInstanceFor($Stats[0])->display();
			?>
		</div>
	</div>

</div>

<?php
$Frontend->displayFooter();
$Frontend->close();
?>