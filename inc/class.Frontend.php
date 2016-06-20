<?php
/**
 * This file contains the class::Frontend to create and print the HTML-Page.
 * @package Runalyze\Frontend
 */

use Runalyze\Configuration;
use Runalyze\Error;
use Runalyze\Timezone;
use Symfony\Component\Yaml\Yaml;


/**
 * Frontend class for setting up everything
 * 
 * The frontend initializes everything for Runalyze.
 * It sets the autoloader, constants and mysql-connection.
 * By default, constructing a new frontend will print a html-header.
 * 
 * Standard initialization of Runalyze:
 * <code>
 *  require 'inc/class.Frontend.php';
 *  $Frontend = new Frontend();
 * </code>
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Frontend
 */
class Frontend {
	/**
	 * Symfony object
	 * @var object
	 */
	protected $symfonyUser = false;


	/**
	 * Admin password as md5
	 * @var string
	 */
	protected $adminPassAsMD5 = '';
	
	/**
	 * Yaml Configuration
	 * @var array
	 */
	protected $yamlConfig = array();

	/**
	 * Constructor
	 * 
	 * Constructing a new Frontend includes all files and sets the correct header.
	 * Runalyze is not usable without setting up the environment with this class.
	 * 
	 * @param bool $hideHeaderAndFooter By default a html-header is directly shown
	 */
	public function __construct($hideHeaderAndFooter = false, $symfonyUser=null) {
		$this->symfonyUser = $symfonyUser;
		$this->initSystem();
		$this->defineConsts();

		if (!$hideHeaderAndFooter)
			$this->displayHeader();
		else
			Error::getInstance()->footer_sent = true;
	}

	/**
	 * Destructor
	 */
	public function __destruct() {
		if (!Error::getInstance()->footer_sent)
			$this->displayFooter();
	}

	/**
	 * Init system 
	 */
	private function initSystem() {
		define('RUNALYZE', true);
		define('FRONTEND_PATH', dirname(__FILE__).'/');

		$this->setAutoloader();
		
		$this->initCache();
		$this->initErrorHandling();
		$this->initConfig();
		$this->initDatabase();
		$this->initSessionAccountHandler();
		$this->initTimezone();
		$this->forwardAccountIDtoDatabaseWrapper();
	}

	/**
	 * Set up Autloader 
	 */
	private function setAutoloader() {
		require_once FRONTEND_PATH.'../vendor/autoload.php';
	}
	
	/**
	 * Setup config
	 */
	private function initConfig() {
	    $config = Yaml::parse(file_get_contents('../data/config.yml'))['parameters'];
	    $this->yamlConfig = $config;
	    define('OPENWEATHERMAP_API_KEY', $config['openweathermap_api_key']);
	    define('NOKIA_HERE_APPID', $config['nokia_here_appid']);
	    define('NOKIA_HERE_TOKEN', $config['nokia_here_token']);
	    define('SMTP_HOST', $config['smtp_host']);
	    define('SMTP_PORT', $config['smtp_port']);
	    define('SMTP_SECURITY', $config['smtp_security']);
	    define('SMTP_USERNAME', $config['smtp_username']);
	    define('SMTP_PASSWORD', $config['smtp_password']);
	    define('MAIL_NAME', $config['mail_name']);
	    define('MAIL_SENDER', $config['mail_sender']);
	    define('PERL_PATH', $config['perl_path']);
	    define('TTBIN_PATH', $config['ttbin_path']);
	    define('GEONAMES_USERNAME', $config['geonames_username']);
	    define('USER_DISABLE_ACCOUNT_ACTIVATION', $config['user_disable_account_activation']);
	    define('SQLITE_MOD_SPATIALITE', $config['sqlite_mod_spatialite']);

	}
	
	/**
	 * Setup timezone
	 */
	private function initTimezone() {
		Timezone::setPHPTimezone(SessionAccountHandler::getTimezone());
		Timezone::setMysql();
	}
                
        /**
	 * Setup Language
	 */
	private function initCache() {
		require_once FRONTEND_PATH.'/system/class.Cache.php';

		try {
			new Cache();
		} catch (Exception $E) {
			die('Cache directory "./'.Cache::PATH.'/cache/" must be writable.');
		}
	}

	/**
	 * Init constants
	 */
	private function defineConsts() {
		require_once FRONTEND_PATH.'system/define.consts.php';

		Configuration::loadAll();

		\Runalyze\Calculation\JD\VDOTCorrector::setGlobalFactor( Configuration::Data()->vdotFactor() );

		require_once FRONTEND_PATH.'class.Helper.php';
	}

	/**
	 * Include class::Error and and initialise it
	 */
	protected function initErrorHandling() {
		\Runalyze\Error::init(Request::Uri());
	}

	/**
	 * Connect to database
	 */
	private function initDatabase() {
		$config = $this->yamlConfig;
		$this->adminPassAsMD5 = md5($config['database_password']);
		define('PREFIX', $config['database_prefix']);
		DB::connect($config['database_host'], $config['database_port'], $config['database_user'], $config['database_password'], $config['database_name']);
	}

	/**
	 * Display admin view
	 */
	public function displayAdminView() {
		$AdminView = new AdminView($this->adminPassAsMD5);
		$AdminView->display();
	}

	/**
	 * Init SessionAccountHandler
	 */
	protected function initSessionAccountHandler() {
		new SessionAccountHandler();
		if (!is_null($this->symfonyUser) && $this->symfonyUser->getToken()->getUser() != 'anon.') {
		    $user = $this->symfonyUser->getToken()->getUser();

		    SessionAccountHandler::setAccount(array(
			    'id' => $user->getId(),
			    'username' => $user->getUsername(),
			    'language' => $user->getLanguage(),
			    'timezone' => $user->getTimezone(),
			    'mail' => $user->getMail(),
		    ));
		}
	}

	/**
	 * Forward accountid to database wraper
	 */
	protected function forwardAccountIDtoDatabaseWrapper() {
		DB::getInstance()->setAccountID( SessionAccountHandler::getId() );
	}

	/**
	 * Display the HTML-Header
	 */
	public function displayHeader() {
	    
		if (!Request::isAjax() && !isset($_GET['hideHtmlHeader']))
			include 'tpl/tpl.Frontend.header.php';

		Error::getInstance()->header_sent = true;
	}

	/**
	 * Display the HTML-Footer
	 */
	public function displayFooter() {
		if (Error::getInstance()->hasErrors()) {
			Error::getInstance()->display();
		}

		if (!Request::isAjax() && !isset($_GET['hideHtmlHeader'])) {
			include 'tpl/tpl.Frontend.footer.php';
		}

		Error::getInstance()->footer_sent = true;
	}

	/**
	 * Display panels
	 */
	public function displayPanels() {
		$Factory = new PluginFactory();
		$Panels = $Factory->enabledPanels();

		foreach ($Panels as $key) {
			$Panel = $Factory->newInstance($key);
			$Panel->display();
		}
	}
}
