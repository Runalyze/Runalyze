<?php
/**
 * This file contains the class to handle every panel-plugin.
 */
/**
 * Class: Panel
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Mysql
 * @uses class::Error
 * @uses $global
 *
 * Last modified 2011/03/05 13:00 by Hannes Christiansen
 */

Error::getInstance()->addTodo('class::Panel set/edit/use Config-vars', __FILE__, 190);

class Panel {
	/**
	 * Internal id in database
	 * @var int
	 */
	private $id;

	/**
	 * Filename
	 * @var string
	 */
	private $filename;

	/**
	 * Number of this panel in order
	 * @var int
	 */
	private $order;

	/**
	 * Integer flag: Is this panel acitve?
	 * @var int
	 */
	private $active;

	/**
	 * Internal config array
	 * @var array
	 */
	private $config;

	/**
	 * Name of this panel
	 * @var string
	 */
	private $name;

	/**
	 * Description of this panel
	 * @var string
	 */
	private $description;

	/**
	 * Symbol(s) on the right
	 * @var string
	 */
	private $right_symbol;

	/**
	 * Constructor (needs an ID)
	 * @param int $id
	 */
	public function __construct($id) {
		global $global;

		if (!is_numeric($id) || $id == NULL) {
			Error::getInstance()->addError('An object of class::Panel must have an ID: <$id='.$id.'>');
			return false;
		}

		$dat = Mysql::getInstance()->fetch('ltb_plugin', $id);
		if ($dat['type'] != 'panel') {
			Error::getInstance()->addError('This plugin (ID='.$id.') is not a panel-plugin.');
			return false;
		}

		$this->id = $id;
		$this->filename = $dat['filename'];
		$this->order = $dat['order'];
		$this->active = $dat['active'];
		$this->name = $dat['name'];
		$this->description = $dat['description'];
		$this->right_symbol = '';

		$this->initConfigVars($dat['config']);
	}

	/**
	 * Initialize all config vars from database
	 * Each line should be in following format: var_name|type=something|description
	 * @param string $config_dat as $dat['config'] from database
	 */
	private function initConfigVars($config_dat) {
		$this->config = array();

		// Config-lines should have following format: var_name|type=something|description
		$config_dat = explode("\n", $config_dat);
		foreach ($config_dat as $line) {
			$parts = explode('|', $line);
			if (count($parts) != 3)
				break;

			$var_str = explode('=', $parts[1]);
			if (count($var_str) == 2) {
				$var = $var_str[1];
				switch ($var_str[0]) {
					case 'bool':
					case 'int':
					case 'floor':
						$type = $var_str[0];
						break;
					default:
						$type = 'string';
				}
			} else {
				$var = $var_str[0];
				$type = 'string';
			}

			$this->config[$parts[0]] = array(
				'type' => $type,
				'var' => $var,
				'description' => trim($parts[2]));
		}
	}

	/**
	 * Function to get a property from object
	 * @param $property
	 * @return mixed      objects property or false if property doesn't exist
	 */
	public function get($property) {
		switch($property) {
			case 'id': return $this->id;
			case 'filename': return $this->filename;
			case 'name': return $this->name;
			case 'description': return $this->description;
			default: Error::getInstance()->addWarning('Asked for non-existant property "'.$property.'" in class::Panel::get()');
				return false;
		}
	}

	/**
	 * Function to set a property of this object
	 * @param $property
	 * @param $value
	 * @return bool       false if property doesn't exist
	 */
	public function set($property, $value) {
		switch($property) {
			case 'name': $this->name = $value;
			case 'description': $this->description = $value;
			default: Error::getInstance()->addWarning('Tried to set non-existant or locked property "'.$property.'" in class::Panel::set()');
				return false;
		}
	}

	/**
	 * Includes the plugin-file for displaying the panels
	 * @param bool $displayDiv
	 */
	public function display($displayDiv = true) {
		global $config, $global;

		$file = FRONTEND_PATH.'plugin/'.$this->filename;
		if (!file_exists($file))
			Error::getInstance()->addError('The plugin-file ('.$file.') does not exist.');
		else {
			require_once('plugin/'.$this->filename);
			if (function_exists(strtolower($this->name).'_rightSymbol'))
				$this->setRightSymbol( call_user_func(strtolower($this->name).'_rightSymbol') );
			else
				Error::getInstance()->addWarning('class::Panel::display(): The function '.strtolower($this->name).'_rightSymbol() does not exist.');

			// Outputs
			if ($displayDiv) echo(NL.'<div class="panel" id="panel-'.$this->id.'">'.NL);
				$this->displayHeader();
				$this->displayContent();
			if ($displayDiv) echo(NL.'</div>'.NL);
		}
	}

	/**
	 * Sets the right symbol for the header of the panel
	 * @param $html
	 */
	private function setRightSymbol($html) {
		$this->right_symbol = $html;
	}

	/**
	 * Displays the h1-header for the panel, including the config-buttons
	 * @return string
	 */
	private function displayHeader() {
		$this->displayConfigDiv();

		echo('
		<span class="right">'.$this->right_symbol.'</span>
	<h1>
		<span class="link clap" rel="'.$this->id.'" title="'.$this->description.'">
			'.$this->name.'
		</span>
	</h1>'.NL);
	}

	/**
	 * Displays the content of the panel using the plugin-own display function
	 */
	public function displayContent() {
		echo(NL.'<div class="content"'.(($this->active == 2) ? ' style="display:none;"' : '' ).'>'.NL);
		if (function_exists(strtolower($this->name).'_display'))
			call_user_func(strtolower($this->name).'_display');
		else
			Error::getInstance()->addWarning('class::Panel::display(): The function '.strtolower($this->name).'_display() does not exist.');
		echo(NL.'</div>'.NL);
	}

	/**
	 * Displays the config container for this panel
	 * @TODO Outsource code as a template
	 */
	public function displayConfigDiv() {
		echo('
	<div class="config">
		'.Ajax::window('<a href="inc/class.Panel.config.php?id='.$this->id.'" title="Plugin bearbeiten"><img src="'.Icon::getSrc(Icon::$CONF_SETTINGS).'" alt="Plugin bearbeiten" /></a>','small').'
		<img class="link up" rel="'.$this->id.'" src="'.Icon::getSrc(Icon::$ARR_UP_BIG).'" alt="Nach oben verschieben" />
		<img class="link down" rel="'.$this->id.'" src="'.Icon::getSrc(Icon::$ARR_DOWN_BIG).'" alt="Nach unten verschieben" />
	</div>'.NL);
	}

	/**
	 * Displays the config window for editing the variables
	 * @TODO Outsource code as a template
	 */
	public function displayConfigWindow() {
		// TODO Plugin deaktivieren
		// TODO wenn vorhanden: Config-Vars bearbeiten
		// TODO Config-Vars muessen Einfluss auf Plugin haben!
		$count_config = sizeof($this->config);

		echo('
	<h1>Konfiguration: '.$this->name.'</h1>
	<small class="right">
		<a href="#" title="Funktion noch nicht vorhanden!">Plugin deaktivieren</a>
	</small>
	<br />
	<strong>Beschreibung:</strong><br />
	'.$this->description.'<br />
	<br />'.NL);
		if ($count_config == 0)
			echo('Es sind <em>keine</em> <strong>Konfigurations-Variablen</strong> vorhanden<br />');
		else {
			echo('<form>');
			foreach ($this->config as $name => $config_var) {
				switch ($config_var['type']) {
					case 'bool':
						echo('<input type="checkbox" name="'.$name.'"'.($config_var['var'] == 'true' ? ' checked="checked"' : '').' /> '.$config_var['description'].'<br />');
						break;
					case 'int':
						echo('<input type="text" name="'.$name.'" value="'.$config_var['var'].'" size="5" /> '.$config_var['description'].'<br />');
						break;
					default:
						echo('<input type="text" name="'.$name.'" value="'.$config_var['var'].'" /> '.$config_var['description'].'<br />');
				}
			}
			echo('</form>');
		}
	}

	/**
	 * Returns the html-link to this statistic for tab-navigation
	 * @return string
	 * @TODO Does not return the correct panel-link!
	 */
	public function getLink() {
		return '<a rel="statistiken" href="plugin/stat.'.$this->filename.'" alt="'.$this->description.'">'.$this->name.'</a>';
	}

	/**
	 * Returns the html-link for inner-html-navigation
	 * @return string
	 * @TODO Does not return the correct panel-link!
	 */
	public function getInnerLink() {
		return '<a class="ajax" target="tab_content" href="plugin/stat.'.$this->filename.'">'.$this->name.'</a>';
	}

	/**
	 * Function to (in)activate the plugin
	 * @param $active bool
	 */
	public function setActive($active = true) {
		Mysql::getInstance()->update('ltb_plugin',$this->id,'active',$active);
	}

	/**
	 * Function to (un)clap the plugin
	 */
	public function clap() {
		if ($this->active == 0) {
			Error::getInstance()->addError('Can\'t clap the panel (ID='.$this->id.') because it\'s not active.');
			return;
		}

		Mysql::getInstance()->update('ltb_plugin', $this->id, 'active', (($this->active == 1) ? 2 : 1));
	}

	/**
	 * Function to move the panel up or down
	 * @param string $mode   'up' | 'down'
	 */
	public function move($mode) {
		if ($mode == 'up') {
			Mysql::getInstance()->query('UPDATE `ltb_plugin` SET `order`='.$this->order.' WHERE `type`="panel" AND `order`='.($this->order-1).' LIMIT 1');
			Mysql::getInstance()->update('ltb_plugin', $this->id, 'order', ($this->order-1));
		} elseif ($mode == 'down') {
			Mysql::getInstance()->query('UPDATE `ltb_plugin` SET `order`='.($this->order).' WHERE `type`="panel" AND `order`='.($this->order+1).' LIMIT 1');
			Mysql::getInstance()->update('ltb_plugin', $this->id, 'order', ($this->order+1));
		}
	}
}
?>