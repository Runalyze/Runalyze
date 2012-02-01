<?php
/**
 * This file contains the abstract class to handle every panel-plugin.
 */
/**
 * Class: PluginPanel
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Mysql
 * @uses class:Error
 */

abstract class PluginPanel extends Plugin {
	/**
	 * Internal flag: Show surrounding div
	 * @var bool
	 */
	public $SurroundingDivIsVisible = true;

	/**
	 * Use only text as right symbol
	 * @var bool
	 */
	protected $textAsRightSymbol = false;

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
		if ($id == parent::$INSTALLER_ID) {
			$this->id = $id;
			return;
		}

		if (!is_numeric($id) || $id <= 0) {
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
		if ($this->SurroundingDivIsVisible)
			echo(NL.'<div class="panel" id="panel-'.$this->id.'">'.NL);
		
		$this->displayConfigDiv();
		$this->displayHeader();

		echo(NL.'<div class="content"'.(($this->active == parent::$ACTIVE_VARIOUS) ? ' style="display:none;"' : '' ).'>'.NL);
		$this->displayContent();
		echo('</div>');

		if ($this->SurroundingDivIsVisible)
			echo(NL.'</div>'.NL);
	}

	/**
	 * Displays the config container for this panel
	 */
	public function displayConfigDiv() {
		echo('
			<div class="config">
				'.Ajax::window('<a href="'.self::$CONFIG_URL.'?id='.$this->id.'" title="Plugin bearbeiten"><img src="'.Icon::getSrc(Icon::$CONF_SETTINGS).'" alt="Plugin bearbeiten" /></a>','small').'
				<img class="link up" rel="'.$this->id.'" src="'.Icon::getSrc(Icon::$ARR_UP_BIG).'" alt="Nach oben verschieben" />
				<img class="link down" rel="'.$this->id.'" src="'.Icon::getSrc(Icon::$ARR_DOWN_BIG).'" alt="Nach unten verschieben" />
			</div>'.NL);
	}

	/**
	 * Display header
	 */
	private function displayHeader() {
		echo('<span class="right '.($this->textAsRightSymbol?'smallHeadNavi':'').'">'.$this->getRightSymbol().'</span>
			<h1>
				<span class="link clap" rel="'.$this->id.'" title="'.$this->description.'">
					'.$this->name.'
				</span>
			</h1>'.NL);
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

		Mysql::getInstance()->update(PREFIX.'plugin', $this->id, 'active', (($this->active == parent::$ACTIVE) ? parent::$ACTIVE_VARIOUS : parent::$ACTIVE));
	}

	/**
	 * Function to move the panel up or down
	 * @param string $mode   'up' | 'down'
	 */
	public function move($mode) {
		if ($mode == 'up') {
			Mysql::getInstance()->query('UPDATE `'.PREFIX.'plugin` SET `order`='.$this->order.' WHERE `type`="panel" AND `order`='.($this->order-1).' LIMIT 1');
			Mysql::getInstance()->update(PREFIX.'plugin', $this->id, 'order', ($this->order-1));
		} elseif ($mode == 'down') {
			Mysql::getInstance()->query('UPDATE `'.PREFIX.'plugin` SET `order`='.($this->order).' WHERE `type`="panel" AND `order`='.($this->order+1).' LIMIT 1');
			Mysql::getInstance()->update(PREFIX.'plugin', $this->id, 'order', ($this->order+1));
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
?>