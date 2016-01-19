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
	 * Type
	 * @return int
	 */
	final public function type() {
		return PluginType::TOOL;
	}

	/**
	 * Includes the plugin-file for displaying the tool
	 */
	public function display() {
		$this->prepareForDisplay();

		if (Request::param('wrap') != "") {
			echo '<div id="pluginTool">';
		}

		echo '<div class="panel-heading">';
		$this->displayHeader();
		echo '</div>';
		echo '<div class="panel-content">';
		$this->displayLongDescription();
		$this->displayContent();
		echo '</div>';

		if (Request::param('wrap') != "") {
			echo '</div>';
		}
	}

	/**
	 * Display header for all tools
	 */
	public static function displayToolsHeader() {
		$Sublinks = array();
		$Sublinks[] = Ajax::link('--- '.__('All tools'), self::$TOOLS_DIV_ID, self::$DISPLAY_URL.'?list=true');

		$Factory = new PluginFactory();

		foreach ($Factory->activePlugins(PluginType::TOOL) as $key) {
			$Sublinks[] = $Factory->newInstance($key)->getLink();
		}

		$Links = array();
		$Links[] = array('tag' => '<a href="#">'.__('Choose tool').'</a>', 'subs' => $Sublinks);

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
		echo '<h1>'.__('Tools').'</h1>';
		echo '</div>';
		echo '<div class="panel-content">';
		echo __('Complex tools can analyze or process the complete database and will open in an overlay.').'<br><br>';

		echo '<table class="fullwidth zebra-style more-padding">';
		echo '<thead><tr><th colspan="3">'.__('Installed tools').':</th></tr></thead>';
		echo '<tbody class="top-and-bottom-border">';

		$Factory = new PluginFactory();
		$tools = $Factory->activePlugins( PluginType::TOOL );
		
		if (empty($tools)) {
			echo '<tr><td colspan="3"><em>'.__('No tools installed.').'.</em></td></tr>';
		}

		foreach ($tools as $key) {
			$Factory = new PluginFactory();
			$Plugin = $Factory->newInstance($key);

			echo '<tr>
					<td>'.$Plugin->getConfigLink().'</td>
					<td class="b">'.self::getLinkFor($Plugin->id(), $Plugin->name()).'</td>
					<td>'.$Plugin->description().'</td>
				</tr>';
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
		echo '<h1>'.$this->name().'</h1>';
		echo '<div class="hover-icons">'.$this->getConfigLink().'</div>';
	}

	/**
	 * Returns the html-link to this plugin for tab-navigation
	 * @param int $id Id for the plugin
	 * @param string $name Name for the link
	 * @param string $data [optional] Additional data
	 * @return string
	 */
	public static function getLinkFor($id, $name, $data = '') {
		return Ajax::link($name, self::$TOOLS_DIV_ID, parent::$DISPLAY_URL.'?id='.$id, $data);
	}

	/**
	 * Returns the html-link to this plugin for tab-navigation
	 * @param string $data Additional data
	 * @return string
	 */
	public function getLink($name = '', $data = '') {
		if ($name == '') {
			$name = $this->name();
		}

		return self::getLinkFor($this->id(), $name, $data);
	}

	/**
	 * Returns the html-link to this plugin
	 * @param string $getParameter
	 * @return string
	 */
	public function getActionLink($name, $getParameter = '') {
		return Ajax::link($name, self::$TOOLS_DIV_ID, parent::$DISPLAY_URL.'?id='.$this->id().'&'.$getParameter);
	}

	/**
	 * Get link to this tool as overlay
	 * @param string $name
	 * @param boolean $wrapAsContainer optional
	 * @return string
	 */
	public function getWindowLink($name = '', $wrapAsContainer = false) {
		if ($name == '') {
			$name = $this->name();
		}

		return Ajax::window('<a href="'.parent::$DISPLAY_URL.'?id='.$this->id().($wrapAsContainer ? '&wrap=true' : '').'">'.$name.'</a>', 'big');
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
		throw new BadMethodCallException('PluginTool does not support getInnerLink().');
	}
}