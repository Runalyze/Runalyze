<?php
/**
 * This file contains class::PluginPanel
 * @package Runalyze\Plugin
 */
/**
 * Abstract plugin class for panels
 * @author Hannes Christiansen
 * @package Runalyze\Plugin
 */
abstract class PluginPanel extends Plugin {
	/**
	 * Internal flag: Show surrounding div
	 * @var bool
	 */
	public $SurroundingDivIsVisible = true;

	/**
	 * Boolean flag: Don't reload if config has changed
	 * @var boolean
	 */
	protected $dontReloadForConfig = false;

	/**
	 * Boolean flag: Don't reload if a training has changed
	 * @var boolean
	 */
	protected $dontReloadForTraining = false;

	/**
	 * Boolean flag: Remove padding from panel-content
	 * @var boolean
	 */
	protected $removePanelContentPadding = false;

	/**
	 * Method for initializing default config-vars (should be implemented in each plugin)
	 */
	protected function getDefaultConfigVars() { return array(); }

	/**
	 * Method for getting the right symbol(s) (implemented in each plugin)
	 */
	protected function getRightSymbol() { return ''; }

	/**
	 * Constructor (needs ID)
	 * @param int $id
	 */
	public function __construct($id) {
		if (!is_numeric($id) || ($id <= 0 && $id != parent::$INSTALLER_ID)) {
			Error::getInstance()->addError('PluginPanel::__construct(): An object of class::Plugin must have an ID: <$id='.$id.'>');
			return false;
		}

		$this->id = $id;
		$this->type = parent::$PANEL;

		$this->initVars();
		$this->initPlugin();
	}

	/**
	 * Includes the plugin-file for displaying the statistics
	 */
	public function display() {
		$this->prepareForDisplay();

		if ($this->SurroundingDivIsVisible) {
			$classes = '';
			if ($this->dontReloadForConfig)
				$classes .= ' '.Plugin::$DONT_RELOAD_FOR_CONFIG_FLAG;
			if ($this->dontReloadForTraining)
				$classes .= ' '.Plugin::$DONT_RELOAD_FOR_TRAINING_FLAG;

			echo '<div class="panel'.$classes.'" id="panel-'.$this->id.'">';
		}

		$this->displayHeader();

		echo '<div class="panel-content'.($this->removePanelContentPadding ? ' nopadding' : '').'"'.(($this->active == parent::$ACTIVE_VARIOUS) ? ' style="display:none;"' : '' ).'>';
		$this->displayContent();
		echo '</div>';

		if ($this->SurroundingDivIsVisible)
			echo '</div>';
	}

	/**
	 * Displays the config container for this panel
	 */
	public function getConfigLinks() {
		$Links = array();

		$Links[] = $this->getConfigLink();
		$Links[] = '<span class="link up" rel="'.$this->id.'">'.Icon::$UP.'</span>';
		$Links[] = '<span class="link down" rel="'.$this->id.'">'.Icon::$DOWN.'</span>';
		$Links[] = $this->getReloadLink();

		return implode('', $Links);
	}

	/**
	 * Display header
	 */
	private function displayHeader() {
		echo '<div class="panel-heading">';
		//echo '<div class="icons-left"></div>';
		echo '<div class="panel-menu">'.$this->getRightSymbol().'</div>';
		echo '<h1 class="link clap" rel="'.$this->id.'">'.$this->name.'</h1>';
		echo '<div class="hover-icons">'.$this->getConfigLinks().'</div>';
		echo '</div>';
	}

	/**
	 * Set flag for visibility of the surrounding div
	 * @param bool $value
	 */
	public function setSurroundingDivVisible($value = true) {
		$this->SurroundingDivIsVisible = $value;
	}

	/**
	 * Function to (un)clap the plugin
	 */
	public function clap() {
		if ($this->active == parent::$ACTIVE_NOT) {
			Error::getInstance()->addError('PluginPanel::clap(): Can\'t clap the panel (ID='.$this->id.') because it\'s not active.');
			return;
		}

		DB::getInstance()->update('plugin', $this->id, 'active', (($this->active == parent::$ACTIVE) ? parent::$ACTIVE_VARIOUS : parent::$ACTIVE));
	}

	/**
	 * Function to move the panel up or down
	 * @param string $mode   'up' | 'down'
	 */
	public function move($mode) {
		if ($mode == 'up') {
			DB::getInstance()->exec('UPDATE `'.PREFIX.'plugin` SET `order`='.$this->order.' WHERE `type`="panel" AND `order`='.($this->order-1).' LIMIT 1');
			DB::getInstance()->update('plugin', $this->id, 'order', ($this->order-1));
		} elseif ($mode == 'down') {
			DB::getInstance()->exec('UPDATE `'.PREFIX.'plugin` SET `order`='.($this->order).' WHERE `type`="panel" AND `order`='.($this->order+1).' LIMIT 1');
			DB::getInstance()->update('plugin', $this->id, 'order', ($this->order+1));
		}
	}

	/**
	 * Returns the html-link to this plugin for tab-navigation
	 * @return string
	 */
	public function getLink() {
		Error::getInstance()->addWarning('PluginPanel::getLink(): For a panel there is no link.');

		return '';
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
		Error::getInstance()->addWarning('PluginPanel::getInnerLink(): For a panel there is no inner link.');

		return '';
	}
}