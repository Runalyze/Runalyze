<?php
/**
 * This file contains the class to handle every panel-plugin.
 */
/**
 * Class: Panel
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Mysql ($mysql)
 * @uses class:Error ($error)
 * @uses $global
 *
 * Last modified 2010/09/07 22:35 by Hannes Christiansen
 */

$error->add('TODO', 'class::Panel set/edit/use Config-vars', __FILE__, 190);

class Panel {
	private $id,
		$filename,
		$order,
		$active,
		$config,
		$name,
		$description,
		$right_symbol;

	function __construct($id) {
		global $error, $mysql, $global;

		if (!is_numeric($id)) {
			$error->add('ERROR','An object of class::Panel must have an ID: <$id='.$id.'>');
			return false;
		}
		$dat = $mysql->fetch('ltb_plugin',$id);
		if ($dat['type'] != 'panel') {
			$error->add('ERROR','This plugin (ID='.$id.') is not a panel-plugin.');
			return false;
		}
		$this->id = $id;
		$this->filename = $dat['filename'];
		$this->order = $dat['order'];
		$this->active = $dat['active'];
		$this->name = $dat['name'];
		$this->description = $dat['description'];
		$this->right_symbol = '';
		// Get config-information from MySql
		$this->config = array();
		$config_dat = explode("\n", $dat['config']);
		foreach ($config_dat as $line) {
			// Config-lines should have following format: var_name|type=something|description
			$parts = explode('|', $line);
			if (sizeof($parts) != 3)
				break;
			$var_str = explode('=', $parts[1]);
			if (sizeof($var_str) == 2) {
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

			$this->config[$parts[0]] = array('type' => $type, 'var' => $var, 'description' => trim($parts[2]));
		}
	}

	/**
	 * Function to get a property from object
	 * @param $property
	 * @return mixed      objects property or false if property doesn't exist
	 */
	function get($property) {
		global $error;

		switch($property) {
			case 'id': return $this->id;
			case 'filename': return $this->filename;
			case 'name': return $this->name;
			case 'description': return $this->description;
			default: $error->add('NOTICE','Asked for non-existant property "'.$property.'" in class::Panel::get()');
				return false;
		}
	}

	/**
	 * Function to set a property of this object
	 * @param $property
	 * @param $value
	 * @return bool       false if property doesn't exist
	 */
	function set($property, $value) {
		global $error;
		
		switch($property) {
			case 'name': $this->name = $value;
			case 'description': $this->description = $value;
			default: $error->add('NOTICE','Tried to set non-existant or locked property "'.$property.'" in class::Panel::set()');
				return false;
		}
	}

	/**
	 * Includes the plugin-file for displaying the statistics
	 */
	function display() {
		global $mysql, $error, $config, $global;

		$file = 'inc/plugin/'.$this->filename;
		if (!file_exists($file))
			$error->add('WARNING','The plugin-file ('.$file.') does not exist.');
		else {
			require_once('plugin/'.$this->filename);
			if (function_exists(strtolower($this->name).'_rightSymbol'))
				$this->setRightSymbol( call_user_func(strtolower($this->name).'_rightSymbol') );
			else
				$error->add('WARNING','class::Panel::display(): The function '.strtolower($this->name).'_rightSymbol() does not exist.');

			// Outputs
			echo(NL.'<div class="panel">'.NL);
				$this->displayHeader();
				$this->displayContent();
			echo(NL.'</div>'.NL);
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
		global $error;

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
	function displayContent() {
		global $error;

		echo(NL.'<div class="content"'.(($this->active == 2) ? ' style="display:none;"' : '' ).'>'.NL);
		if (function_exists(strtolower($this->name).'_display'))
			call_user_func(strtolower($this->name).'_display');
		else
			$error->add('WARNING','class::Panel::display(): The function '.strtolower($this->name).'_display() does not exist.');
		echo(NL.'</div>'.NL);
	}

	/**
	 * Displays the config container for this panel
	 */
	function displayConfigDiv() {
		echo('
	<div class="config">
		'.Ajax::window('<a href="inc/class.Panel.config.php?id='.$this->id.'" title="Plugin bearbeiten"><img src="img/confSettings.png" alt="Plugin bearbeiten" /></a>','small').'
		<img class="link up" rel="'.$this->id.'" src="img/arrUp.png" alt="Nach oben verschieben" />
		<img class="link down" rel="'.$this->id.'" src="img/arrDown.png" alt="Nach unten verschieben" />
	</div>'.NL);
	}

	/**
	 * Displays the config window for editing the variables
	 */
	function displayConfigWindow() {
		// TODO Plugin deaktivieren
		// TODO wenn vorhanden: Config-Vars bearbeiten
		// TODO Config-Vars müssen Einfluss auf Plugin haben!
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
	 * @TODO Does not return the right panel-link!
	 */
	function getLink() {
		return '<a rel="statistiken" href="plugin/stat.'.$this->filename.'" alt="'.$this->description.'">'.$this->name.'</a>';
	}

	/**
	 * Returns the html-link for inner-html-navigation
	 * @return string
	 * @TODO Does not return the right panel-link!
	 */
	function getInnerLink() {
		return '<a class="ajax" target="tab_content" href="plugin/stat.'.$this->filename.'">'.$this->name.'</a>';
	}

	/**
	 * Function to (in)activate the plugin
	 * @param $active bool
	 */
	function setActive($active = true) {
		global $mysql, $error;

		$mysql->update('ltb_plugin',$this->id,'active',$active);
	}

	/**
	 * Function to (un)clap the plugin
	 */
	function clap() {
		global $mysql, $error;

		if ($this->active == 0) {
			$error->add('ERROR','Can\'t clap the panel (ID='.$this->id.') because it\'s not active.');
			return;
		}
		$mysql->update('ltb_plugin',$this->id,'active',(($this->active == 1) ? 2 : 1));
	}

	/**
	 * Function to move the panel up or down
	 * @param $mode   'up' | 'down'
	 */
	function move($mode) {
		global $mysql, $error;

		if ($mode == 'up') {
			$mysql->query('UPDATE `ltb_plugin` SET `order`='.$this->order.' WHERE `type`="panel" AND `order`='.($this->order-1).' LIMIT 1');
			$mysql->update('ltb_plugin',$this->id,'order',($this->order-1));
		} elseif ($mode == 'down') {
			$mysql->query('UPDATE `ltb_plugin` SET `order`='.($this->order).' WHERE `type`="panel" AND `order`='.($this->order+1).' LIMIT 1');
			$mysql->update('ltb_plugin',$this->id,'order',($this->order+1));
		}
	}
}
?>