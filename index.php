<?php
/**
 * RUNALYZE
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @copyright http://www.runalyze.de/
 */
if (!file_exists('config.php')) {
	include 'install.php';
	exit();
}

require 'inc/class.Frontend.php';

$Frontend = new Frontend();
?>

<div id="container">
	<div id="main">
		<div id="data-browser" class="panel">
			<div id="data-browser-inner">
				<?php
				$DataBrowser = new DataBrowser();
				$DataBrowser->display();
				?>
			</div>
		</div>


		<ul id="statistics-nav">
			<?php
			$Stats = Plugin::getKeysAsArray(Plugin::$STAT, Plugin::$ACTIVE);
			foreach ($Stats as $i => $key) {
				$Plugin = Plugin::getInstanceFor($key);
				if ($Plugin !== false)
					echo '<li'.($i == 0 ? ' class="active"' : '').'>'.$Plugin->getLink().'</li>'.NL;
			}

			if (PluginStat::hasVariousStats())
				echo '<li>'.PluginStat::getLinkForVariousStats().'</li>';
			?>
		</ul>
		<div id="statistics" class="panel">
			<div id="statistics-inner">
				<?php
				if (empty($Stats))
					echo('<em>Es sind keine Statistiken vorhanden. Du musst sie in der Konfiguration aktivieren.</em>');
				else
					Plugin::getInstanceFor($Stats[0])->display();
				?>
			</div>
		</div>

	</div>

	<div id="panels">
		<?php $Frontend->displayPanels(); ?>
	</div>
</div>