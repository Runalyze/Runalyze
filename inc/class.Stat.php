<?php
/**
 * This file contains the class to handle every statistic-plugin.
 */
/**
 * Class: Stat
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Mysql
 * @uses class:Error
 * @uses $global
 *
 * Last modified 2011/03/05 13:00 by Hannes Christiansen
 */

Error::getInstance()->addTodo('class::Stat: Set config like in class::Panel?');

class Stat {
	/**
	 * Internal ID from database
	 * @var int
	 */
	private $id;

	/**
	 * Integer flag: Is this statistic acitve?
	 * @var int
	 */
	private $active;

	/**
	 * Array with all config vars
	 * @var array
	 */
	private $config;

	/**
	 * Filename
	 * @var string
	 */
	private $filename;

	/**
	 * Name of this statistic
	 * @var string
	 */
	private $name;

	/**
	 * Description
	 * @var string
	 */
	private $description;

	/**
	 * Internal sport-ID from database
	 * @var int
	 */
	private $sportid;

	/**
	 * Displayed year
	 * @var int
	 */
	private $year;

	/**
	 * Internal data from database
	 * @var array
	 */
	private $dat;

	/**
	 * Constructor (needs ID)
	 * @param int $id
	 public */
	function __construct($id) {
		global $global;

		if (!is_numeric($id) || $id == NULL) {
			Error::getInstance()->addError('An object of class::Stat must have an ID: <$id='.$id.'>');
			return false;
		}

		$dat = Mysql::getInstance()->fetch('ltb_plugin',$id);
		if ($dat['type'] != 'stat') {
			Error::getInstance()->addError('This plugin (ID='.$id.') is not a statistic-plugin.');
			return false;
		}

		$this->id = $id;
		$this->active = $dat['active'];
		$this->filename = $dat['filename'];
		$this->name = $dat['name'];
		$this->description = $dat['description'];
		$this->sportid = $global['hauptsport'];
		$this->year = date('Y');
		$this->dat = '';

		if (isset($_GET['sport']))
			if (is_numeric($_GET['sport']))
				$this->sportid = $_GET['sport'];
		if (isset($_GET['jahr']))
			if (is_numeric($_GET['jahr']))
				$this->year = $_GET['jahr'];
		if (isset($_GET['dat']))
			$this->dat = $_GET['dat'];

		$this->initConfigVars($dat['config']);
	}

	/**
	 * Initialize all config vars from database
	 * Each line should be in following format: var_name|type=something|description
	 * @param string $config_dat as $dat['config'] from database
	 */
	private function initConfigVars($config_dat) {
		Error::getInstance()->addTodo('Move config-setting to class::Plugin');

		$this->config = array();
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
			case 'config': return $this->config;
			case 'filename': return $this->filename;
			case 'name': return $this->name;
			case 'description': return $this->description;
			case 'sportid': return $this->sportid;
			case 'year': return $this->year;
			case 'dat': return $this->dat;
			default: Error::getInstance()->addWarning('Asked for non-existant property "'.$property.'" in class::Stat::get()');
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
			case 'sportid': $this->sportid = $value;
			case 'year': $this->year = $value;
			case 'dat': $this->dat = $value;
			default: Error::getInstance()->addWarning('Tried to set non-existant or locked property "'.$property.'" in class::Stat::set()');
				return false;
		}
	}

	/**
	 * Includes the plugin-file for displaying the statistics
	 */
	public function display() {
		global $config, $global;

		if ($this->active == 2)
			$this->displayVariousStatistics();

		include('plugin/'.$this->filename);
	}

	/**
	 * Display links to all various statistics
	 */
	public function displayVariousStatistics() {
		echo(NL.'<small class="right">'.NL);
		$others = Mysql::getInstance()->fetch('SELECT `id` FROM `ltb_plugin` WHERE `type`="stat" AND `active`=2 ORDER BY `order` ASC', false, true);
		foreach($others as $i => $other) {
			if ($i != 0)
				echo(' | ');
			$Stat = new Stat($other['id']);
			echo $Stat->getInnerLink($Stat->get('name'));
		}
		echo(NL.'</small>'.NL);
	}

	/**
	 * Displays the config window for editing the variables
	 */
	public function displayConfigWindow() {
		// TODO Outsource
		// TODO Plugin deaktivieren
		// TODO wenn vorhanden: Config-Vars bearbeiten
		// TODO Config-Vars müssen Einfluss auf Plugin haben!
		$count_config = count($this->config);

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
	 */
	public function getLink() {
		if ($this->active == 2)
			return '<a rel="statistiken" href="inc/class.Stat.display.php?id='.$this->id.'" alt="Kleinere Statistiken">Sonstiges</a>';
		return '<a rel="statistiken" href="inc/class.Stat.display.php?id='.$this->id.'" alt="'.$this->description.'">'.$this->name.'</a>';
	}

	/**
	 * Returns the html-link for inner-html-navigation
	 * @param $name     displayed link-name
	 * @param $sport    id of sport, default $this->sportid
	 * @param $year     year, default $this->year
	 * @param $dat      optional dat-parameter
	 * @return string
	 */
	public function getInnerLink($name, $sport = 0, $year = 0, $dat = '') {
		if ($sport == 0)
			$sport = $this->sportid;
		if ($year == 0)
			$year = $this->year;
		return '<a class="ajax" target="tab_content" href="inc/class.Stat.display.php?id='.$this->id.'&sport='.$sport.'&jahr='.$year.'&dat='.$dat.'">'.$name.'</a>';
	}

	/**
	 * Function to (in)activate the plugin
	 * @param $active bool
	 */
	public function setActive($active = true) {
		Mysql::getInstance()->update('ltb_plugin',$this->id,'active',$active);
	}
}
?>