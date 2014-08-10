<?php
/**
 * This file contains class::Plugin
 * @package Runalyze\Plugin
 */
/**
 * Abstract class for Plugins
 * @author Hannes Christiansen
 * @package Runalyze\Plugin
 */
abstract class Plugin {
	/**
	 * Enum: inactive
	 * @var int
	 */
	const ACTIVE_NOT = 0;

	/**
	 * Enum: active
	 * @var int
	 */
	const ACTIVE = 1;

	/**
	 * Enum: various/hidden
	 * @var int
	 */
	const ACTIVE_VARIOUS = 2;

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
	 * CSS-Flag for plugins: Don't reload if config has changed
	 * @var string
	 */
	public static $DONT_RELOAD_FOR_CONFIG_FLAG = 'dont-reload-for-config';

	/**
	 * CSS-Flag for plugins: Don't reload if a training has changed
	 * @var string
	 */
	public static $DONT_RELOAD_FOR_TRAINING_FLAG = 'dont-reload-for-training';

	/**
	 * Internal ID from database
	 * @var int
	 */
	private $id;

	/**
	 * Pluginkey
	 * @var string
	 */
	private $key;

	/**
	 * Integer flag: Is this statistic acitve?
	 * @var int
	 */
	private $active;

	/**
	 * Integer position of plugins
	 * @var int
	 */
	private $order;

	/**
	 * Array with all config vars
	 * @var array
	 */
	protected $config;

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
	 * Constructor (needs ID)
	 * @param int $id
	 */
	public function __construct($id) {
		$this->id = $id;
		$this->key = get_class($this);

		if ($id == PluginInstaller::ID) {
			return;
		}

		if (!is_numeric($id) || $id <= 0) {
			throw new RuntimeException('Invalid id "'.$id.'" given to create a plugin.');
		}

		$this->initVars();
		$this->initPlugin();
	}

	/**
	 * Plugin key
	 * @return string
	 */
	final public function key() {
		return $this->key;
	}

	/**
	 * ID
	 * @return int
	 */
	final public function id() {
		return $this->id;
	}

	/**
	 * Name
	 * @return string
	 */
	abstract public function name();

	/**
	 * Description
	 * @return string
	 */
	abstract public function description();

	/**
	 * Type
	 * @return int
	 */
	abstract public function type();

	/**
	 * Type as string
	 * @return string
	 */
	final public function typeString() {
		return PluginType::string( $this->type() );
	}

	/**
	 * Is active?
	 * @return bool
	 */
	final public function isActive() {
		return ($this->active == self::ACTIVE);
	}

	/**
	 * Is inactive?
	 * @return bool
	 */
	final public function isInActive() {
		return ($this->active == self::ACTIVE_NOT);
	}

	/**
	 * Is hidden/various?
	 * @return bool
	 */
	final public function isHidden() {
		return ($this->active == self::ACTIVE_VARIOUS);
	}

	/**
	 * Order
	 * @return int
	 */
	final public function order() {
		return $this->order;
	}

	/**
	 * Method for initializing everything (implemented in each plugin)
	 */
	protected function initPlugin() {}

	/**
	 * Method for initializing default config-vars (implemented in each plugin)
	 */
	protected function getDefaultConfigVars() { return array(); }

	/**
	 * Includes the plugin-file for displaying the plugin (implemented in subclass)
	 */
	abstract public function display();

	/**
	 * Inits all data (implemented in each plugin)
	 */
	protected function prepareForDisplay() {}

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
	 * Display description, can be overwritten for displaying a longer description 
	 */
	protected function displayLongDescription() {
		echo HTML::p($this->description());
	}

	/**
	 * Install this plugin
	 * @return bool
	 */
	final public function install() {
		if ($this->id() != PluginInstaller::ID) {
			Error::getInstance()->addError('Plugin can not be installed, id is set wrong.');
			return false;
		}

		$this->id = DB::getInstance()->insert('plugin', array(
			'key',
			'type',
			'active',
			'order',
		), array(
			$this->key(),
			$this->typeString(),
			'1',
			'99',
		));

		$this->config = $this->getDefaultConfigVars();
		$this->updateConfigVarToDatabase();

		return true;
	}

	/**
	 * Initialize all variables
	 */
	final protected function initVars() {
		if ($this->id() == PluginInstaller::ID) {
			return;
		}

		$dat = DB::getInstance()->fetchByID('plugin', $this->id());

		$this->key         = $dat['key'];
		$this->active      = $dat['active'];
		$this->order       = $dat['order'];
		$this->sportid     = $this->defaultSport();
		$this->year        = $this->defaultYear();
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
		$this->checkConfigVarsForChanges();
	}

	/**
	 * Default sport
	 * 
	 * May be overwritten in subclass.
	 * Default setting: CONF_MAINSPORT
	 * 
	 * @return int sportid, can be -1 for all sports
	 */
	protected function defaultSport() {
		return CONF_MAINSPORT;
	}

	/**
	 * Default year
	 * 
	 * May be overwritten in subclass.
	 * Default setting: current year
	 * 
	 * @return int year, can be -1 for no year/comparison of all years
	 */
	protected function defaultYear() {
		return date('Y');
	}

	/**
	 * Title for all years
	 * @return string
	 */
	protected function titleForAllYears() {
		return __('Year on year');
	}

	/**
	 * Initialize all config vars from database
	 * Each line should be in following format: var_name|type=something|description
	 * @param string $configSetup as $dat['config'] from database
	 */
	private function initConfigVars($configSetup) {
		$this->config     = array();
		$configSetup      = explode("\n", $configSetup);

		foreach ($configSetup as $configLine) {
			$configParts = explode('|', $configLine);

			if (count($configParts) != 3)
				break;

			$valueParts = explode('=', $configParts[1]);
			if (count($valueParts) == 2) {
				$value = $valueParts[1];
				switch ($valueParts[0]) {
					case 'array':
						$type  = 'array';
						$value = explode(',', $value);
						break;
					case 'bool':
						$value = ($value == 'true');
					case 'int':
					case 'float':
						$type  = $valueParts[0];
						break;
					default:
						$type  = 'string';
				}
			} else {
				$value = $valueParts[0];
				$type  = 'string';
			}

			$this->config[$configParts[0]] = array(
				'type'			=> $type,
				'var'			=> $value,
				'description'	=> trim($configParts[2]));
		}
	}

	private function checkConfigVarsForChanges() {
		$somethingChanged = false;

		$this->checkConfigVarsForMissingValues($somethingChanged);
		$this->checkConfigVarsForAdditionalValue($somethingChanged);

		if ($somethingChanged) {
			$this->updateConfigVarToDatabase();
		}
	}

	/**
	 * Check config vars: Are any values from default config vars missing?
	 * @param boolean $somethingChanged 
	 */
	private function checkConfigVarsForMissingValues(&$somethingChanged) {
		$defaultSetup = $this->getDefaultConfigVars();

		foreach ($defaultSetup as $key => $keyArray) {
			if (!isset($this->config[$key])) {
				$somethingChanged   = true;
				$this->config[$key] = $keyArray;
			} elseif ($this->config[$key]['description'] != $keyArray['description']) {
				$somethingChanged   = true;
				$this->config[$key]['description'] = $keyArray['description'];
			}
		}
	}

	/**
	 * Check config vars: Are any values not in default config vars?
	 * @param boolean $somethingChanged 
	 */
	private function checkConfigVarsForAdditionalValue(&$somethingChanged) {
		$defaultSetup = $this->getDefaultConfigVars();

		foreach (array_keys($this->config) as $key) {
			if (!isset($defaultSetup[$key])) {
				$somethingChanged   = true;
				unset($this->config[$key]);
			}
		}	
	}

	/**
	 * Get config
	 * @return array
	 */
	final public function getConfig() {
		return $this->config;
	}

	/**
	 * Get link for the config-window
	 * @param string $name [optional], default: settings-icon
	 * @param string $add_param [optional] additional parameter starting with '&...'
	 * @return string
	 */
	final public function getConfigLink($name = '', $add_param = '') {
		if ($name == '') {
			$name = Icon::$CONF;
		}

		return Ajax::window('<a href="'.self::$CONFIG_URL.'?id='.$this->id().$add_param.'">'.$name.'</a>','small');
	}

	/**
	 * Get reload-link
	 * @return string
	 */
	final public function getReloadLink() {
		return '<span class="link" onclick="Runalyze.reloadPlugin(\''.$this->id().'\');">'.Icon::$REFRESH_SMALL.'</span>';
	}

	/**
	 * Handle Get/Post-data and update database
	 */
	private function handleGetPostRequest() {
		if (isset($_GET['active'])) {
			$this->setActive((int) $_GET['active']);
		}

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
	final public function displayConfigWindow() {
		$this->handleGetPostRequest();

		include FRONTEND_PATH.'plugin/tpl.Plugin.config.php';
	}

	/**
	 * Get input-field for configuration formular
	 * @param string $name
	 * @param array $config_var
	 * @return string 
	 */
	final protected function getInputFor($name, $config_var) {
		$value = (is_array($config_var['var'])) ? implode(', ', $config_var['var']) : $config_var['var'];

		switch ($config_var['type']) {
			case 'bool':
				return '<input id="conf_'.$name.'" class="" type="checkbox" name="'.$name.'"'.($config_var['var'] == 'true' ? ' checked' : '').'>';
			case 'array':
				return '<input id="conf_'.$name.'" class="full-size" type="text" name="'.$name.'" value="'.$value.'">';
			case 'int':
				return '<input id="conf_'.$name.'" class="small-size" type="text" name="'.$name.'" value="'.$value.'">';
			default:
				return '<input id="conf_'.$name.'" class="middle-size" type="text" name="'.$name.'" value="'.$value.'">';
		}
	}

	/**
	 * Update current values from $this->config to database
	 */
	final protected function updateConfigVarToDatabase() {
		$string = '';
		foreach($this->config as $name => $dat) {
			switch ($dat['type']) {
				case 'array':
					$var = implode(', ', Helper::arrayTrim($dat['var']));
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

		DB::getInstance()->update('plugin', $this->id(), 'config', $string);
	}

	/**
	 * Function to (in)activate the plugin
	 * @param int $active
	 */
	final public function setActive($active = 1) {
		DB::getInstance()->update('plugin', $this->id(), 'active', $active);
		$this->active = $active;
	}
}