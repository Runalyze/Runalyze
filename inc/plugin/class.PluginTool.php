<?php
/**
 * This file contains class::PluginTool
 * @package Runalyze\Plugin
 */
/**
 * Abstract plugin class for tools
 * @author Hannes Christiansen
 * @package Runalyze\Plugin
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
		$this->prepareForDisplay();

		if (Request::param('wrap') != "")
			echo '<div id="pluginTool">';

		echo '<div class="panel-heading">';
		$this->displayHeader();
		echo '</div>';
		echo '<div class="panel-content">';
		echo '<p class="text">'.$this->description.'</p>';
		$this->displayContent();
		echo '</div>';

		if (Request::param('wrap') != "")
			echo '</div>';
	}

	/**
	 * Display header for all tools
	 */
	public static function displayToolsHeader() {
		$Sublinks = array();
		$Sublinks[] = Ajax::link('--- Alle Tools', self::$TOOLS_DIV_ID, self::$DISPLAY_URL.'?list=true');

		$tools = DB::getInstance()->query('SELECT `id`, `name` FROM `'.PREFIX.'plugin` WHERE `type`="'.self::getTypeString(self::$TOOL).'" AND `active`='.self::$ACTIVE.' ORDER BY `order` ASC')->fetchAll();
		foreach ($tools as $tool)
			$Sublinks[] = self::getLinkFor($tool['id'], $tool['name']);

		$Links = array();
		$Links[] = array('tag' => '<a href="#">Tool w&auml;hlen</a>', 'subs' => $Sublinks);

		echo '<div class="panel-menu panel-menu-floated">';
		echo Ajax::toolbarNavigation($Links);
		echo '</div>';
	}

	/**
	 * Display surrounding div and default content for all tools
	 */
	public static function displayToolsContent() {
		echo '<div id="'.self::$TOOLS_DIV_ID.'">';
		echo '<div class="panel-heading">';
		echo '<h1>Tools</h1>';
		echo '</div>';
		echo '<div class="panel-content">';
		echo 'Mit Tools kannst du komplizierte &Auml;nderungen in der Datenbank vornehmen oder Daten extrahieren.<br><br>';

		echo '<table class="fullwidth zebra-style more-padding">';
		echo '<thead><tr><th colspan="3">Installierte Tools:</th></tr></thead>';
		echo '<tbody class="top-and-bottom-border">';

		$tools = self::getKeysAsArray(self::$TOOL, self::$ACTIVE);
		
		if (empty($tools))
			echo '<tr><td colspan="3"><em>Keine Plugins vorhanden.</em></td></tr>';
		
		foreach ($tools as $key) {
			$Plugin = Plugin::getInstanceFor($key);

			echo('
				<tr>
					<td>'.$Plugin->getConfigLink().'</td>
					<td class="b">'.self::getLinkFor($Plugin->get('id'), $Plugin->get('name')).'</td>
					<td>'.$Plugin->get('description').'</td>
				</tr>');
		}
				
		echo '</tbody>';
		echo '</table>';
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Display header
	 */
	private function displayHeader() {
		echo '<h1>'.$this->name.'</h1>';
		echo '<div class="hover-icons">'.$this->getConfigLink().'</div>';
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
	 * Returns the html-link to this plugin
	 * @param string $getParameter
	 * @return string
	 */
	public function getActionLink($name, $getParameter = '') {
		return Ajax::link($name, self::$TOOLS_DIV_ID, parent::$DISPLAY_URL.'?id='.$this->id.'&'.$getParameter);
	}

	/**
	 * Get link to this tool as overlay
	 * @param string $name
	 * @param boolean $wrapAsContainer optional
	 */
	public function getWindowLink($name = '', $wrapAsContainer = false) {
		if ($name == '')
			$name = $this->name;

		return Ajax::window('<a href="'.parent::$DISPLAY_URL.'?id='.$this->id.($wrapAsContainer ? '&wrap=true' : '').'" title="'.$this->name.'">'.$name.'</a>', 'big');
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