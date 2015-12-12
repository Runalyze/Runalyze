<?php
/**
 * This file contains class::Plugin
 * @package Runalyze\Plugin
 */

use Runalyze\Configuration;
use Runalyze\Error;

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
	 * Internal sport-ID from database
	 * @var int
	 */
	protected $sportid;

	/**
	 * Displayed year (-1: 'all', 6/12: 'last 6/12 months')
	 * @var int
	 */
	protected $year;

	/**
	 * Internal data from database
	 * @var string
	 */
	protected $dat;

	/**
	 * Configuration
	 * @var PluginConfiguration
	 */
	private $Configuration;

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

		$this->initConfiguration();
		$this->initVars();
		$this->initPlugin();
	}

	/**
	 * Init configuration
	 * 
	 * May be used in subclass to set own configuration.
	 * Make sure to add all values to the configuration object
	 * before using <code>$this->setConfiguration($Configuration);</code>.
	 */
	protected function initConfiguration() {
		$Configuration = new PluginConfiguration($this->id);

		$this->setConfiguration($Configuration);
	}

	/**
	 * Set configuration
	 * @param PluginConfiguration $Configuration
	 */
	protected function setConfiguration(PluginConfiguration &$Configuration) {
		$this->Configuration = $Configuration;
	}

	/**
	 * Configuration
	 * 
	 * This method call will force the configuration object to catch its values
	 * from the database if not already done.
	 * @return PluginConfiguration
	 */
	final public function &Configuration() {
		$this->Configuration->catchValuesFromDatabaseIfNotDoneYet();

		return $this->Configuration;
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

		return true;
	}
        
	/**
	 * Initialize all variables
	 */
	final protected function initVars() {
		if ($this->id() == PluginInstaller::ID) {
			return;
		}

        $data = PluginFactory::dataFor($this->id());
		$this->key     = $data['key'];
		$this->active  = $data['active'];
		$this->order   = $data['order'];
		$this->sportid = $this->defaultSport();
		$this->year    = $this->defaultYear();
		$this->dat     = '';

		if (isset($_GET['sport']))
			if (is_numeric($_GET['sport']))
				$this->sportid = $_GET['sport'];
		if (isset($_GET['jahr']))
			if (is_numeric($_GET['jahr']))
				$this->year = $_GET['jahr'];
		if (isset($_GET['dat']))
			$this->dat = $_GET['dat'];
	}

	/**
	 * @return boolean
	 */
	final protected function showsAllYears() {
		return ($this->year == -1);
	}

	/**
	 * @return boolean
	 */
	final protected function showsSpecificYear() {
		return ($this->year != -1 && $this->year != 6 && $this->year != 12);
	}

	/**
	 * @return boolean
	 */
	final protected function showsTimeRange() {
		return ($this->year == 6) || ($this->year == 12);
	}

	/**
	 * @return boolean
	 */
	final protected function showsLast6Months() {
		return ($this->year == 6);
	}

	/**
	 * @return boolean
	 */
	final protected function showsLast12Months() {
		return ($this->year == 12);
	}

	/**
	 * Default sport
	 * 
	 * May be overwritten in subclass.
	 * Default setting: Configuration::General()->mainSport()
	 * 
	 * @return int sportid, can be -1 for all sports
	 */
	protected function defaultSport() {
		return Configuration::General()->mainSport();
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

			Ajax::setReloadFlag(Ajax::$RELOAD_ALL);
			echo Ajax::getReloadCommand();
		}
	}

	/**
	 * Displays the config window for editing the variables
	 */
	final public function displayConfigWindow() {
		$this->handleGetPostRequest();

		$Window = new PluginConfigurationWindow($this);
		$Window->display();
	}

	/**
	 * Function to (in)activate the plugin
	 * @param int $active
	 */
	final public function setActive($active = 1) {
		DB::getInstance()->update('plugin', $this->id(), 'active', $active);
		$this->active = $active;

		PluginFactory::clearCache();
	}
}