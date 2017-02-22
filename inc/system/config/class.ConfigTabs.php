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
	public static $CONFIG_URL = 'settings';

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
        $Links = [array('tag' => Ajax::link(__('General settings'), self::$TABS_ID, 'settings?key=config_tab_general')),
                array('tag' => Ajax::link(__('Plugins'), self::$TABS_ID, 'settings?key=config_tab_plugins')),
                array('tag' => Ajax::link(__('Dataset'), self::$TABS_ID, 'settings/dataset')),
                array('tag' => Ajax::link(__('Sports'), self::$TABS_ID, 'settings?key=config_tab_sports')),
                array('tag' => Ajax::link(__('Activity Types'), self::$TABS_ID, 'settings?key=config_tab_types')),
                array('tag' => Ajax::link(__('Equipment'), self::$TABS_ID, 'settings?key=config_tab_equipment')),
                array('tag' => Ajax::link(__('Account'), self::$TABS_ID, 'settings/account'))];

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
