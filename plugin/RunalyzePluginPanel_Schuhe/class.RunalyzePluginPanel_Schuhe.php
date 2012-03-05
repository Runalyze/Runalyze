<?php
/**
 * This file contains the class of the RunalyzePluginPanel "Schuhe".
 */
$PLUGINKEY = 'RunalyzePluginPanel_Schuhe';
/**
 * Class: RunalyzePluginPanel_Schuhe
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Plugin
 * @uses class::PluginPanel
 * @uses class::Mysql
 * @uses inc/draw/plugin.schuhe.php
 */
class RunalyzePluginPanel_Schuhe extends PluginPanel {
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
		return Ajax::window('<a href="plugin/'.$this->key.'/window.schuhe.php">'.Icon::get(Icon::$ADD, '', '', 'Laufschuh hinzuf&uuml;gen').'</a>');
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		echo('<div id="schuhe">');

		$inuse = true;
		$schuhe = Mysql::getInstance()->fetchAsArray('SELECT `id`, `name`, `km`, `inuse` FROM `'.PREFIX.'shoe` ORDER BY `inuse` DESC, `km` DESC');
		foreach ($schuhe as $i => $schuh) {
			if ($inuse && $schuh['inuse'] == 0) {
				echo('<div id="hiddenschuhe" style="display:none;">'.NL);
				$inuse = false;
			}

			echo('
			<p style="background-image:url(plugin/'.$this->key.'/schuhbalken.php?km='.round($schuh['km']).');">
				<span class="right">'.Helper::Km($schuh['km']).'</span>
				<strong>'.DataBrowser::getSearchLink($schuh['name'], 'opt[shoeid]=is&val[shoeid][0]='.$schuh['id']).'</strong>
			</p>'.NL);	
		}

		if (!$inuse)
			echo '</div>';
		echo '</div>';

		echo Ajax::toggle('<a class="right" href="#schuhe" name="schuhe">Alte Schuhe anzeigen</a>', 'hiddenschuhe');
		echo HTML::clearBreak();
	}
}
?>