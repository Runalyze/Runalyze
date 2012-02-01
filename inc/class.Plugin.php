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
 */
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
	* Enum for plugin-type: Tool
	* @var int
	*/
	public static $TOOL = 2;

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
	 * Url for displaying the install-window
	 * @var string
	 */
	public static $INSTALL_URL = 'call/call.Plugin.install.php';

	/**
	 * Url for displaying the config-window
	 * @var string
	 */
	public static $CONFIG_URL = 'call/call.Plugin.config.php';

	/**
	 * Url for displaying the plugin
	 * @var string
	 */
	public static $DISPLAY_URL = 'call/call.Plugin.display.php';

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
	 * Integer position of plugins
	 * @var int
	 */
	protected $order;

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
	 * @return Plugin
	 */
	static public function getInstanceFor($PLUGINKEY) {
		$pluginFile = self::getFileForKey($PLUGINKEY);

		if ($pluginFile === false) {
			Error::getInstance()->addError('Can\'t find plugin-file or -directory in system.');
			return false;
		}

		include_once $pluginFile;

		if (!class_exists($PLUGINKEY)) {
			Error::getInstance()->addError('The plugin-file must contain class::'.$PLUGINKEY.'.');
			return false;
		} else {
			$dat = Mysql::getInstance()->fetchSingle('SELECT `id` FROM `'.PREFIX.'plugin` WHERE `key`="'.$PLUGINKEY.'"');
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
	 * Get array with all avaiable plugins for installation
	 * @return array
	 */
	static public function getPluginsToInstallAsArray() {
		$plugins   = array();
		$dir = opendir(FRONTEND_PATH.'../plugin/');
		while ($file = readdir($dir)) {
			if (substr($file, 0, 6) == 'class.')
				$key = substr($file, 6, -4);
			elseif (strpos($file, '.') === false && is_dir(FRONTEND_PATH.'../plugin/'.$file))
				$key = $file;
			else
				continue;

			if (!self::isInstalled($key))
				$plugins[] = array('key' => $key);
		}

		closedir($dir);

		return $plugins;
	}

	/**
	 * Get link to the window for installing this plugin
	 * @param string $name
	 * @return string
	 */
	public function getInstallLink($name = '') {
		if ($name == '')
			$name = Icon::get(Icon::$ADD, 'Plugin installieren');

		return Ajax::window('<a href="'.self::$INSTALL_URL.'?key='.$this->key.'" title="Plugin installieren">'.$name.'</a>');
	}

	/**
	 * Install a new plugin
	 * @param string $key
	 */
	static public function installPlugin($key) {
		$file = self::getFileForKey($key);

		if ($file === false) {
			Error::getInstance()->addError('Pluginfile for \''.$key.'\' can\'t be found. Installing impossible.');
			return false;
		}

		include_once $file;

		if (!isset($PLUGINKEY)) {
			Error::getInstance()->addError('$PLUGINKEY must be set in the pluginfile \''.$file.'\'.');
			return false;
		} elseif (substr($PLUGINKEY, 0, 14) != 'RunalyzePlugin') {
			Error::getInstance()->addError('$PLUGINKEY must start with \'RunalyzePlugin\', but it is\''.$PLUGINKEY.'\'.');
			return false;
		}

		$Plugin = self::getInstanceFor($PLUGINKEY);
		return $Plugin->install();
	}

	/**
	 * Install this plugin
	 * @return bool
	 */
	public function install() {
		if ($this->id != self::$INSTALLER_ID) {
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
			self::getTypeString($this->type),
			'class.'.$this->key.'.php',
			$this->name,
			$this->description,
			'99',
			);

		$this->id = Mysql::getInstance()->insert(PREFIX.'plugin', $columns, $values);
		$this->config = $this->getDefaultConfigVars();

		$this->setActive(1);
		$this->updateConfigVarToDatabase();

		return true;
	}

	/**
	 * Initialize all variables
	 */
	protected function initVars() {
		if ($this->id == self::$INSTALLER_ID)
			return;

		$dat = Mysql::getInstance()->fetch(PREFIX.'plugin', $this->id);

		$this->active      = $dat['active'];
		$this->order       = $dat['order'];
		$this->filename    = $dat['filename'];
		$this->name        = $dat['name'];
		$this->description = $dat['description'];
		$this->sportid     = CONF_MAINSPORT;
		$this->year        = date('Y');
		$this->dat         = '';

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
		// TODO: Add vars to config if not in DB
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
					case 'float':
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
			case 'active': return $this->active;
			case 'order': return $this->order;
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
				switch ($dat['type']) {
					case 'array':
						$array = explode(',', $_POST[$name]);
						foreach ($array as $i => $var)
							$array[$i] = trim($var);
						$this->config[$name]['var'] = $array;
						break;
					case 'bool':
						$this->config[$name]['var'] = isset($_POST[$name]) && ($_POST[$name] == 'on');
						break;
					case 'int':
						$this->config[$name]['var'] = Helper::CommaToPoint(trim($_POST[$name]));
						break;
					default:
						$this->config[$name]['var'] = trim($_POST[$name]);
				}

				$this->updateConfigVarToDatabase();
			}
		}
	}

	/**
	 * Displays the config window for editing the variables
	 */
	public function displayConfigWindow() {
		$this->handleGetPostRequest();

		$activationLink = ($this->active == 0)
			? $this->getConfigLink('Plugin aktivieren', '&active='.Plugin::$ACTIVE)
			: $this->getConfigLink('Plugin deaktivieren', '&active='.Plugin::$ACTIVE_NOT);

		$name = ($this instanceof PluginTool)
			? $this->getWindowLink()
			: $this->name;

		echo('
			<h1>Konfiguration: '.$name.'</h1>
			<small class="right">
				'.$activationLink.'
			</small><br />

			<p class="text-headline">Beschreibung</p>
			<p class="text">
				'.$this->description.'
			</p>'.NL);

		if ($this->active == self::$ACTIVE_NOT)
			echo '<p class="warning">Das Plugin ist derzeit deaktiviert.</p>';

		if (count($this->config) == 0)
			echo '<p class="info">Es sind <em>keine</em> Optionen vorhanden</p>';
		else {
			echo '<form action="'.self::$CONFIG_URL.'?id='.$this->id.'" class="ajax" id="pluginconfig" method="post">';
			echo '<p class="text-headline">Einstellungen</p>';
			echo '<p class="text">';
				foreach ($this->config as $name => $config_var) {
					switch ($config_var['type']) {
						case 'array':
							echo('<label><input type="text" name="'.$name.'" value="'.implode(', ', $config_var['var']).'" /> '.$config_var['description'].'</label><br />');
							break;
						case 'bool':
							echo('<label><input type="checkbox" name="'.$name.'"'.($config_var['var'] == 'true' ? ' checked="checked"' : '').' /> '.$config_var['description'].'</label><br />');
							break;
						case 'int':
							echo('<label><input type="text" name="'.$name.'" value="'.$config_var['var'].'" size="5" /> '.$config_var['description'].'</label><br />');
							break;
						default:
							echo('<label><input type="text" name="'.$name.'" value="'.$config_var['var'].'" /> '.$config_var['description'].'</label><br />');
					}
				}
				echo '<input type="hidden" name="edit" value="true" />';
				echo '<input type="submit" value="Bearbeiten" />';
			echo '</p>';
			echo '</form>';
		}
	}

	/**
	 * Update current values from $this->config to database
	 */
	protected function updateConfigVarToDatabase() {
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

		Mysql::getInstance()->update(PREFIX.'plugin', $this->id, 'config', $string);
	}

	/**
	 * Function to (in)activate the plugin
	 * @param int $active
	 */
	public function setActive($active = 1) {
		Mysql::getInstance()->update(PREFIX.'plugin', $this->id, 'active', $active);
		$this->active = $active;
	}

	/**
	 * Get string for internal type-enum
	 * @param enum $type
	 * @return string
	 */
	static protected function getTypeString($type) {
		switch ($type) {
			case self::$STAT:
				return 'stat';
			case self::$PANEL:
				return 'panel';
			case self::$TOOL:
				return 'tool';
		}
	}

	/**
	 * Get readable string for internal type-enum
	 * @param enum $type
	 * @return string
	 */
	static public function getReadableTypeString($type) {
		switch ($type) {
			case self::$STAT:
				return 'Statistik';
			case self::$PANEL:
				return 'Panel';
			case self::$TOOL:
				return 'Tool';
		}
	}

	/**
	 * Get all keys for a given plugintype as array
	 * @param enum $type [optional]
	 * @param enum $active [optional]
	 * @return array
	 */
	static public function getKeysAsArray($type = -1, $active = -1) {
		if ($type == -1)
			$array = Mysql::getInstance()->fetchAsArray('SELECT `key` FROM `'.PREFIX.'plugin`');
		elseif ($active == -1)
			$array = Mysql::getInstance()->fetchAsArray('SELECT `key` FROM `'.PREFIX.'plugin` WHERE `type`="'.self::getTypeString($type).'" ORDER BY `order` ASC');
		else
			$array = Mysql::getInstance()->fetchAsArray('SELECT `key` FROM `'.PREFIX.'plugin` WHERE `type`="'.self::getTypeString($type).'" AND `active`="'.$active.'" ORDER BY `order` ASC');

		$return = array();
		foreach ($array as $k => $v)
			$return[] = $v['key'];

		return $return;
	}

	/**
	 * Is the plugin already installed?
	 * @param string $key
	 * @return bool
	 */
	static public function isInstalled($key) {
		return in_array($key, self::getKeysAsArray(-1));
	}

	/**
	 * Get the PLUGINKEY for a given ID from database
	 * @param int $id
	 * @return string
	 */
	static public function getKeyForId($id) {
		$dat = Mysql::getInstance()->fetch(PREFIX.'plugin', $id);

		if ($dat === false) {
			Error::getInstance()->addError('Plugin::getKeyForId(): No Plugin with id \''.$id.'\' found.');
			return '';
		}

		return $dat['key'];
	}

	/**
	 * Get the filename for a given PLUGINKEY, searches for folder first
	 * @param string $PLUGINKEY
	 * @return string
	 */
	static public function getFileForKey($PLUGINKEY) {
		if (file_exists(FRONTEND_PATH.'../plugin/'.$PLUGINKEY.'/class.'.$PLUGINKEY.'.php'))
			return FRONTEND_PATH.'../plugin/'.$PLUGINKEY.'/class.'.$PLUGINKEY.'.php';

		if (file_exists(FRONTEND_PATH.'../plugin/class.'.$PLUGINKEY.'.php'))
			return FRONTEND_PATH.'../plugin/class.'.$PLUGINKEY.'.php';

		return false;
	}
}
?>