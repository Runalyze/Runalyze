<?php
/**
 * This file contains the class of the RunalyzePlugin "SchuhePanel".
 */
$PLUGINKEY = 'RunalyzePlugin_SchuhePanel';
/**
 * Class: RunalyzePlugin_SchuhePanel
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Plugin
 * @uses class::PluginPanel
 * @uses class::Mysql
 * @uses inc/draw/plugin.schuhe.php
 *
 * Last modified 2011/07/10 16:00 by Hannes Christiansen
 */
class RunalyzePlugin_SchuhePanel extends PluginPanel {
	/**
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$PANEL;
		$this->name = 'Schuhe';
		$this->description = 'Anzeige der gelaufenen Kilometer aller Schuhe.';
	}

	/**
	 * Set default config-variables
	 * @see PluginPanel::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();

		return $config;
	}

	/**
	 * Method for getting the right symbol(s)
	 * @see PluginPanel::getRightSymbol()
	 */
	protected function getRightSymbol() {
		return Ajax::window('<a href="inc/plugin/window.schuhe.php" title="Schuh hinzuf&uuml;gen">'.Icon::get(Icon::$RUNNINGSHOE, 'Schuh hinzuf&uuml;gen').'</a>');
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		echo('<div id="schuhe">');

		$inuse = true;
		$schuhe = Mysql::getInstance()->fetchAsArray('SELECT `id`, `name`, `km`, `inuse` FROM `ltb_schuhe` ORDER BY `inuse` DESC, `km` DESC');
		foreach ($schuhe as $i => $schuh) {
			if ($inuse && $schuh['inuse'] == 0) {
				echo('<div id="hiddenschuhe" style="display:none;">'.NL);
				$inuse = false;
			}

			echo('
			<p style="background-image:url(inc/draw/plugin.schuhe.php?km='.round($schuh['km']).');">
				<span>'.Helper::Km($schuh['km']).'</span>
				<strong>'.DataBrowser::getSearchLink($schuh['name'], 'opt[schuhid]=is&val[schuhid][0]='.$schuh['id']).'</strong>
			</p>'.NL);	
		}

		echo('</div></div>');

		echo Ajax::toggle('<a class="right" href="#schuhe" name="schuhe">Alte Schuhe anzeigen</a>', 'hiddenschuhe');
		echo Helper::clearBreak();
	}
}
?>