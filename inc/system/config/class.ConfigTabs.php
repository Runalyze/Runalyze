<?php
/**
 * This file contains class::ConfigTabs
 * @package Runalyze\System\Config
 */
/**
 * ConfigTabs
 * @author Hannes Christiansen
 * @package Runalyze\System\Config
 */
class ConfigTabs {
	/**
	 * URL for config-window
	 * @var string
	 */
	public static $CONFIG_URL = 'call/window.config.php';

	/**
	 * HTML-ID for div
	 * @var string
	 */
	public static $TABS_ID = 'config-tabs';

	/**
	 * Messages to show after submit
	 * @var array
	 */
	private static $Messages = array();

	/**
	 * Internal array with all tabs
	 * @var array
	 */
	protected $Tabs = array();

	/**
	 * Default key
	 * @var string
	 */
	protected $defaultKey = '';

	/**
	 * Add message after submit
	 * @param string $HTMLcode HTML::info() or HTML::text() or HTML::warning() or HTML::error()
	 */
	public static function addMessage($HTMLcode) {
		self::$Messages[] = $HTMLcode;
	}

	/**
	 * Add a tab and set it as the default one
	 * @param ConfigTab $Tab 
	 */
	public function addDefaultTab(ConfigTab $Tab) {
		$this->defaultKey = $Tab->getKey();
		$this->addTab($Tab);
	}

	/**
	 * Add a tab
	 * @param ConfigTab $Tab 
	 */
	public function addTab(ConfigTab $Tab) {
		$this->Tabs[$Tab->getKey()] = $Tab;
	}

	/**
	 * Display navigation 
	 */
	protected function displayNavigation() {
		$Links   = array();

		foreach ($this->Tabs as $Tab) {
			$Links[] = array('tag' => Ajax::link($Tab->getTitle(), self::$TABS_ID, $Tab->getUrl()));
		}

		echo Ajax::toolbarNavigation($Links);
	}

	/**
	 * Display tabs 
	 */
	public function display() {
		if (Request::param('form') == 'true') {
			$this->Tabs[$this->getCurrentKey()]->parsePostData();

			$SubmitInfo = '<p class="okay"><em>'.__('The settings have been saved.').'</em></p>';

			if (!empty(self::$Messages))
				$SubmitInfo .= implode('', self::$Messages);

			echo '<div class="panel-heading" id="submit-info">'.$SubmitInfo.'</div>';
			echo Ajax::getReloadCommand();
		}

		if ($this->hasToShowDiv()) {
			echo '<div class="panel-menu panel-menu-floated">';
			$this->displayNavigation();
			echo '</div>';

			echo '<div id="'.self::$TABS_ID.'">';
		}

		$this->displayCurrentTab();

		if ($this->hasToShowDiv()) {
			echo '</div>';
		}
	}

	/**
	 * Has to show surrounding div and navigation?
	 * @return boolean
	 */
	private function hasToShowDiv() {
		return (Request::param('key') == '' || Request::param('form') == 'true' || Request::param('external') == 'true');
	}

	/**
	 * Display current tab 
	 */
	protected function displayCurrentTab() {
		$CurrentKey = $this->getCurrentKey();

		if (isset($this->Tabs[$CurrentKey])) {
			$this->Tabs[$CurrentKey]->display();
		}
	}

	/**
	 * Get current key
	 * @return string
	 */
	protected function getCurrentKey() {
		$CurrentKey = Request::param('key');

		if (empty($CurrentKey)) {
			$CurrentKey = $this->defaultKey;
		}

		return $CurrentKey;
	}
}