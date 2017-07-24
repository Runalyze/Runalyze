<?php
/**
 * RUNALYZE
 *
 * @author Hannes Christiansen
 * @copyright http://www.runalyze.com/
 */

use Runalyze\View\Activity\Context;

if (!isset($request) || !$request->isXmlHttpRequest()) {
	include 'inc/tpl/tpl.Frontend.header.php';
}
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
				} else {
					$showFirstPlugin = true;

					if (isset($_GET['pluginid'])) {
						$Plugin = $Factory->newInstanceFor((int)$_GET['pluginid']);

						if (!$Plugin->isInActive()) {
							$showFirstPlugin = false;
							$Plugin->display();
						}
					}

					if ($showFirstPlugin) {
						if (empty($Stats)) {
							echo __('<em>There are no statistics available. Activate a plugin in your configuration.</em>');
						} else {
							$Factory->newInstance($Stats[0])->display();
						}
					}
				}
				?>
			</div>
		</div>

	</div>

	<div id="panels">
		<?php echo $panelsContent; ?>
	</div>
</div>
<?php
if (!isset($request) || !$request->isXmlHttpRequest()) {
	include 'inc/tpl/tpl.Frontend.footer.php';
}
?>
