<?php
/**
 * This file contains the abstract class to handle every plugin.
 */
/**
 * Class: Plugin
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Mysql
 * @uses class:Error
 *
 * Last modified 2011/07/10 13:00 by Hannes Christiansen
 */
Error::getInstance()->addTodo('class::Plugin: Test Plugin-Installer.');
abstract class Plugin {
	/**
	 * Enum for plugin-type: Statistic
	 * @var int
	 */
	public static $STAT = 0;
	
	/**
	* Enum for plugin-type: Panel
	* @var int
	*/
	public static $PANEL = 1;
	
	/**
	* Enum for plugin-type: Draw
	* @var int
	*/
	public static $DRAW = 2;

	/**
	* Enum for active-flag: hidden
	* @var int
	*/
	public static $ACTIVE_NOT = 0;

	/**
	* Enum for active-flag: active
	* @var int
	*/
	public static $ACTIVE = 1;

	/**
	* Enum for active-flag: various/clapped
	* @var int
	*/
	public static $ACTIVE_VARIOUS = 2;

	/**
	 * Url for displaying the config-window
	 * @var string
	 */
	public static $CONFIG_URL = 'inc/class.Plugin.config.php';

	/**
	 * Id for not installed plugin
	 * @var int
	 */
	public static $INSTALLER_ID = -1;

	/**
	 * Internal ID from database
	 * @var int
	 */
	protected $id;

	/**
	 * Pluginkey (classname)
	 * @var string
	 */
	public $key;

	/**
	 * Internal plugin-type-id
	 * @var int
	 */
	protected $type;

	/**
	 * Integer flag: Is this statistic acitve?
	 * @var int
	 */
	protected $active;

	/**
	 * Array with all config vars
	 * @var array
	 */
	protected $config;

	/**
	 * Filename
	 * @var string
	 */
	protected $filename;

	/**
	 * Name of this plugin
	 * @var string
	 */
	protected $name;

	/**
	 * Description
	 * @var string
	 */
	protected $description;

	/**
	 * Internal sport-ID from database
	 * @var int
	 */
	protected $sportid;

	/**
	 * Displayed year
	 * @var int
	 */
	protected $year;

	/**
	 * Internal data from database
	 * @var array
	 */
	protected $dat;

	/**
	 * Method for initializing everything (implemented in each plugin)
	 */
	abstract protected function initPlugin();

	/**
	 * Method for initializing default config-vars (implemented in each plugin)
	 */
	abstract protected function getDefaultConfigVars();

	/**
	 * Includes the plugin-file for displaying the plugin (implemented in subclass)
	 */
	abstract public function display();

	/**
	 * Method for displaying the content (implemented in each plugin)
	 */
	abstract protected function displayContent();

	/**
	 * Returns the html-link to this plugin for tab-navigation
	 * @return string
	 */
	abstract public function getLink();

	/**
	 * Returns the html-link for inner-html-navigation
	 * @param string $name displayed link-name
	 * @param int $sport id of sport, default $this->sportid
	 * @param int $year year, default $this->year
	 * @param string $dat optional dat-parameter
	 * @return string
	 */
	abstract protected function getInnerLink($name, $sport = 0, $year = 0, $dat = '');

	/**
	 * Get an instance for a given pluginkey (starting with 'RunalyzePlugin');
	 * @param string $PLUGINKEY
	 * @return object
	 */
	static public function getInstanceFor($PLUGINKEY) {
		include_once('plugin/class.'.$PLUGINKEY.'.php');

		if (!class_exists($PLUGINKEY)) {
			Error::getInstance()->addError('Can\'t find \'plugin/class.'.$PLUGINKEY.'.php\'.');
			return false;
		} else {
			$dat = Mysql::getInstance()->fetchSingle('SELECT `id` FROM `ltb_plugin` WHERE `key`="'.$PLUGINKEY.'"');
			if ($dat === false)
				$id = self::$INSTALLER_ID;
			else
				$id = $dat['id'];

			$Plugin =  new $PLUGINKEY($id);
			$Plugin->key = $PLUGINKEY;

			return $Plugin;
		}
	}

	/**
	 * Install a new plugin
	 * @param string $file Filepath relative to inc/plugin/
	 */
	static public function installPlugin($file) {
		if (!file_exists($file)) {
			Error::getInstance()->addError('Pluginfile\''.$file.'\' can\'t be found. Installing impossible.');
			return false;
		}

		include_once($file);

		if (!isset($PLUGINKEY)) {
			Error::getInstance()->addError('$PLUGINKEY must be set in the pluginfile \''.$file.'\'.');
			return false;
		} elseif (substr($PLUGINKEY, 0, 15) != 'RunalyzePlugin_') {
			Error::getInstance()->addError('$PLUGINKEY must start with \'RunalyzePlugin_\', but it is\''.$PLUGINKEY.'\'.');
			return false;
		}

		$Plugin = self::getInstanceFor($PLUGINKEY);
		$Plugin->install();
	}

	/**
	 * Install this plugin
	 */
	public function install() {
		if ($id != self::$INSTALLER_ID) {
			Error::getInstance()->addError('Plugin can not be installed, id is set wrong.');
			return false;
		}

		$columns = array(
			'key',
			'type',
			'filename',
			'name',
			'description',
			'order',
			);
		$values  = array(
			$this->key,
			$this->getTypeString(),
			'class.'.$this->key.'.php',
			$this->name,
			$this->description,
			'99',
			);

		$this->id = Mysql::getInstance()->insert('ltb_plugin', $columns, $values);
		$this->config = $this->getDefaultConfigVars();

		$this->setActive(1);
		$this->updateConfigVarToDatabase();
	}

	/**
	 * Initialize all variables
	 */
	protected function initVars() {
		if ($this->id == self::$INSTALLER_ID)
			return;

		$dat = Mysql::getInstance()->fetch('ltb_plugin', $this->id);

		$this->active = $dat['active'];
		$this->filename = $dat['filename'];
		$this->name = $dat['name'];
		$this->description = $dat['description'];
		$this->sportid = MAINSPORT;
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
					case 'array':
						$type = 'array';
						$var  = explode(',', $var);
						break;
					case 'bool':
						$var = ($var == 'true');
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
			case 'type': return $this->type;
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
	 * Get link for the config-window
	 * @param string $name [optional], default: settings-icon
	 * @param string $add_param [optional] additional parameter starting with '&...'
	 * @return string
	 */
	public function getConfigLink($name = '', $add_param = '') {
		if ($name == '')
			$name = Icon::get(Icon::$CONF_SETTINGS, 'Plugin bearbeiten');

		return Ajax::window('<a href="'.self::$CONFIG_URL.'?id='.$this->id.$add_param.'" title="Plugin bearbeiten">'.$name.'</a>','small');
	}

	/**
	 * Handle Get/Post-data and update database
	 */
	private function handleGetPostRequest() {
		if (isset($_GET['active']))
			$this->setActive((int)$_GET['active']);

		if (isset($_POST['edit']) && $_POST['edit'] == 'true') {
			foreach($this->config as $name => $dat) {
				switch ($config_var['type']) {
					case 'array':
						$array = explode(',', $_POST[$name]);
						foreach ($array as $i => $var)
							$array[$i] = trim($var);
						$this->config[$name]['var'] = $array;
						break;
					case 'bool':
						$this->config[$name]['var'] = ($_POST[$name] == 'on');
						break;
					case 'int':
						$this->config[$name]['var'] = Helper::CommaToPoint(trim($_POST[$name]));
						break;
					default:
						$this->config[$name]['var'] = trim($_POST[$name]);
				}
			}

			$this->updateConfigVarToDatabase();
		}
	}

	/**
	 * Displays the config window for editing the variables
	 */
	public function displayConfigWindow() {
		$this->handleGetPostRequest();

		echo('
			<h1>Konfiguration: '.$this->name.'</h1>
			<small class="right">
				'.$this->getConfigLink('Plugin deaktivieren', '&active=0').'
			</small><br />

			<strong>Beschreibung:</strong><br />
			'.$this->description.'<br />
			<br />'.NL);

		if ($this->active == self::$ACTIVE_NOT)
			echo('<em>Das Plugin ist derzeit deaktiviert.</em><br /><br />');

		if (count($this->config) == 0)
			echo('Es sind <em>keine</em> <strong>Konfigurations-Variablen</strong> vorhanden<br />');
		else {
			echo('<form action="'.self::$CONFIG_URL.'?id='.$this->id.'" method="post">');
			foreach ($this->config as $name => $config_var) {
				switch ($config_var['type']) {
					case 'array':
						echo('<input type="text" name="'.$name.'" value="'.implode(', ', $config_var['var']).'" /> '.$config_var['description'].'<br />');
						break;
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
			echo('<input type="hidden" name="edit" value="true" />');
			echo('<input type="submit" value="Bearbeiten" />');
			echo('</form>');
		}
	}

	/**
	 * Update current values from $this->config to database
	 */
	private function updateConfigVarToDatabase() {
		$string = '';
		foreach($this->config as $name => $dat) {
			switch ($dat['type']) {
				case 'array':
					$var = implode(', ', $dat['var']);
					break;
				case 'bool':
					$var = $dat['var'] ? 'true' : 'false';
					break;
				case 'int':
				default:
					$var = $dat['var'];
			}

			$string .= $name.'|'.$dat['type'].'='.$var.'|'.trim($dat['description']).NL;
		}
		return;

		Mysql::getInstance()->update('ltb_plugin', $this->id, 'config', $string);
	}

	/**
	 * Function to (in)activate the plugin
	 * @param int $active
	 */
	public function setActive($active = true) {
		Mysql::getInstance()->update('ltb_plugin', $this->id, 'active', $active);
	}

	/**
	 * Get string for internal type-enum
	 * @return string
	 */
	private function getTypeString() {
		switch ($this->type) {
			case self::$STAT:
				return 'stat';
			case self::$PANEL:
				return 'panel';
			case self::$Draw:
				return 'draw';
		}
	}

	/**
	 * Get the PLUGINKEY for a given ID from database
	 * @param int $id
	 * @return string
	 */
	static public function getKeyForId($id) {
		$dat = Mysql::getInstance()->fetch('ltb_plugin', $id);

		if ($dat === false) {
			Error::getInstance()->addError('Plugin::getKeyForId(): No Plugin with id \''.$id.'\' found.');
			return '';
		}

		return $dat['key'];
	}
}
?>