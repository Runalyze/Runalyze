<?php
/**
 * This file contains the abstract class to handle every tool-plugin.
 */
/**
 * Class: PluginTool
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Mysql
 * @uses class:Error
 */

abstract class PluginTool extends Plugin {
	/**
	 * Url for displaying the plugin
	 * @var string
	 */
	public static $DISPLAY_URL = 'call/call.PluginTool.display.php';

	/**
	 * Surrounding div for every tool
	 * @var string
	 */
	public static $TOOLS_DIV_ID = 'pluginTool';

	/**
	 * Method for initializing default config-vars (should be implemented in each plugin)
	 */
	protected function getDefaultConfigVars() { return array(); }

	/**
	 * Constructor (needs ID)
	 * @param int $id
	 */
	public function __construct($id) {
		if (!is_numeric($id) || ($id <= 0 && $id != parent::$INSTALLER_ID)) {
			Error::getInstance()->addError('PluginTool::__construct(): An object of class::Plugin must have an ID: <$id='.$id.'>');
			return false;
		}

		$this->id = $id;
		$this->type = parent::$TOOL;

		$this->initVars();
		$this->initPlugin();
	}

	/**
	 * Includes the plugin-file for displaying the tool
	 */
	public function display() {
		$this->displayHeader();
		$this->displayContent();
	}

	/**
	 * Display header for all tools
	 */
	public static function displayToolsHeader() {
		echo '<small class="right">'.NL;
		echo Ajax::link('Alle Tools:', self::$TOOLS_DIV_ID, self::$DISPLAY_URL.'?list=true').NL;

		$tools = Mysql::getInstance()->fetchAsArray('SELECT `id`, `name` FROM `'.PREFIX.'plugin` WHERE `type`="'.self::getTypeString(self::$TOOL).'" AND `active`='.self::$ACTIVE.' ORDER BY `order` ASC');
		foreach ($tools as $i => $tool) {
			if ($i != 0)
				echo ' | ' ;

			echo self::getLinkFor($tool['id'], $tool['name']);
		}

		echo '</small>'.NL;
	}

	/**
	 * Display surrounding div and default content for all tools
	 */
	public static function displayToolsContent() {
		echo '<div id="'.self::$TOOLS_DIV_ID.'">'.NL;
		echo '<h1>Tools</h1>'.NL;
		echo 'Mit Tools kannst du komplizierte &Auml;nderungen in der Datenbank vornehmen oder Daten extrahieren.<br /><br />'.NL;
		echo 'Folgende Tools sind installiert:'.NL;
		echo '<table>'.NL;
		echo HTML::spaceTR(3);

		$tools = self::getKeysAsArray(self::$TOOL, self::$ACTIVE);
		
		if (empty($tools))
			echo '<tr><td colspan="3"><em>Keine Plugins vorhanden.</em></td></tr>';
		
		foreach ($tools as $i => $key) {
			$Plugin = Plugin::getInstanceFor($key);

			echo('
				<tr class="a'.($i%2+1).'">
					<td>'.$Plugin->getConfigLink().'</td>
					<td class="b">'.self::getLinkFor($Plugin->get('id'), $Plugin->get('name')).'</td>
					<td class="small">'.$Plugin->get('description').'</td>
				</tr>');
		}
				
		echo HTML::spaceTR(3);
		echo '</table>'.NL;
		echo '</div>'.NL;
	}

	/**
	 * Display header
	 */
	private function displayHeader() {
		echo '<span class="left margin-5">'.$this->getConfigLink().'</span>'.NL;

		echo '<h1>'.$this->name.'</h1>'.NL;
		echo $this->description.'<br />'.NL;
		echo '<br />'.NL;
	}

	/**
	 * Get link to the tools-overlay
	 * @return string
	 */
	static public function getOverlayLink() {
		return Ajax::window('<a class="left" href="'.self::$DISPLAY_URL.'" title="Tools">'.Icon::get(Icon::$CONF_TOOL, '', '', 'Tools').'</a>');
	}

	/**
	 * Returns the html-link to this plugin for tab-navigation
	 * @param int $id Id for the plugin
	 * @param string $name Name for the link
	 * @param string $data [optional] Additional data
	 * @return string
	 */
	static public function getLinkFor($id, $name, $data = '') {
		return Ajax::link($name, self::$TOOLS_DIV_ID, parent::$DISPLAY_URL.'?id='.$id, $data);
	}

	/**
	 * Returns the html-link to this plugin for tab-navigation
	 * @param string $data Additional data
	 * @return string
	 */
	public function getLink($name = '', $data = '') {
		if ($name == '')
			$name = $this->name;

		return self::getLinkFor($this->id, $name, $data);
	}

	/**
	 * Get link to this tool as overlay
	 */
	public function getWindowLink() {
		return Ajax::window('<a href="'.parent::$DISPLAY_URL.'?id='.$this->id.'" title="'.$this->name.'">'.$this->name.'</a>', 'big');
	}

	/**
	 * Returns the html-link for inner-html-navigation
	 * @param string $name displayed link-name
	 * @param int $sport id of sport, default $this->sportid
	 * @param int $year year, default $this->year
	 * @param string $dat optional dat-parameter
	 * @return string
	 */
	protected function getInnerLink($name, $sport = 0, $year = 0, $dat = '') {
		Error::getInstance()->addWarning('PluginTool::getInnerLink(): For a tool there is no inner link.');

		return '';
	}
}
?>