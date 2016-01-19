<?php
/**
 * RUNALYZE
 * 
 * @author Hannes Christiansen
 * @copyright http://www.runalyze.de/
 */

use Runalyze\View\Activity\Context;

if (!file_exists('data/config.php')) {
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

		<div id="statistics" class="panel">
			<ul id="statistics-nav">
				<?php
				$Factory = new PluginFactory();
				$Stats = $Factory->activePlugins( PluginType::STAT );
				foreach ($Stats as $i => $key) {
					$Plugin = $Factory->newInstance($key);

					if ($Plugin !== false) {
						echo '<li'.($i == 0 ? ' class="active"' : '').'>'.$Plugin->getLink().'</li>';
					}
				}

				if (PluginStat::hasVariousStats()) {
					echo '<li class="with-submenu">';
					echo '<a href="#">'.__('Miscellaneous').'</a>';
					echo '<ul class="submenu">';

					$VariousStats = $Factory->variousPlugins();
					foreach ($VariousStats as $key) {
						$Plugin = $Factory->newInstance($key);

						if ($Plugin !== false) {
							echo '<li>'.$Plugin->getLink().'</li>';
						}
					}

					echo '</ul>';
					echo '</li>';
				}
				?>
			</ul>
			<div id="statistics-inner">
				<?php
				if (isset($_GET['id'])) {
					$Context = new Context(Request::sendId(), SessionAccountHandler::getId());
					$View = new TrainingView($Context);
					$View->display();
				} elseif (isset($_GET['pluginid'])) {
					$Factory->newInstanceFor((int)$_GET['pluginid'])->display();
				} else {
					if (empty($Stats)) {
						echo __('<em>There are no statistics available. Activate a plugin in your configuration.</em>');
					} else {
						$Factory->newInstance($Stats[0])->display();
					}
				}
				?>
			</div>
		</div>

	</div>

	<div id="panels">
		<?php $Frontend->displayPanels(); ?>
	</div>
</div>
