<?php
/**
 * This file contains the class::Frontend to create and print the HTML-Page.
 * @package Runalyze\Frontend
 */

use Runalyze\Configuration;
use Runalyze\Error;

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
	 * URL for help-window
	 * @var string
	 */
	public static $HELP_URL = 'inc/tpl/tpl.help.php';

	/**
	 * Boolean flag: log GET- and POST-data
	 * @var bool
	 */
	protected $logGetAndPost = false;

	/**
	 * Admin password as md5
	 * @var string
	 */
	protected $adminPassAsMD5 = '';

	/**
	 * Constructor
	 * 
	 * Constructing a new Frontend includes all files and sets the correct header.
	 * Runalyze is not usable without setting up the environment with this class.
	 * 
	 * @param bool $hideHeaderAndFooter By default a html-header is directly shown
	 */
	public function __construct($hideHeaderAndFooter = false) {
		$this->initSystem();
		$this->defineConsts();
		$this->checkConfigFile();

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
		date_default_timezone_set('Europe/Berlin');

		$this->initLanguage();
		$this->setAutoloader();
		$this->initCache();
		$this->initErrorHandling();
		$this->initDatabase();
		$this->initDebugMode();
		$this->initSessionAccountHandler();
		$this->forwardAccountIDtoDatabaseWrapper();
	}

	/**
	 * Set up Autloader 
	 */
	private function setAutoloader() {
		require_once FRONTEND_PATH.'/system/class.Autoloader.php';
		new Autoloader();
	}

	/**
	 * Setup Language
	 */
	private function initLanguage() {
		require_once FRONTEND_PATH.'/system/class.Language.php';
		new Language();
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
	 * Check and update if needed config file
	 */
	private function checkConfigFile() {
		AdminView::checkAndUpdateConfigFile();
	}

	/**
	 * Include class::Error and and initialise it
	 */
	protected function initErrorHandling() {
		\Runalyze\Error::init(Request::Uri());

		if ($this->logGetAndPost) {
			if (!empty($_POST))
				Error::getInstance()->addDebug('POST-Data: '.print_r($_POST, true));
			if (!empty($_GET))
				Error::getInstance()->addDebug('GET-Data: '.print_r($_GET, true));
		}
	}

	/**
	 * Connect to database
	 */
	private function initDatabase() {
		require_once FRONTEND_PATH.'../data/config.php';

		$this->adminPassAsMD5 = md5($password);

		DB::connect($host, $port, $username, $password, $database);
		unset($host, $port, $username, $password, $database);
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
	}

	/**
	 * Forward accountid to database wraper
	 */
	protected function forwardAccountIDtoDatabaseWrapper() {
		DB::getInstance()->setAccountID( SessionAccountHandler::getId() );
	}

	/**
	 * Init internal debug-mode. Can be defined in config.php - otherwise is set to false here
	 */
	protected function initDebugMode() {
		if (!defined('RUNALYZE_DEBUG'))
			define('RUNALYZE_DEBUG', false);

		if (RUNALYZE_DEBUG)
			error_reporting(E_ALL);
		else
			Error::getInstance()->setLogVars(true);
	}

	/**
	 * Set correct character encoding 
	 */
	final public function setEncoding() {
		header('Content-type: text/html; charset=utf-8');
		mb_internal_encoding("UTF-8");
	}

	/**
	 * Display the HTML-Header
	 */
	public function displayHeader() {
		$this->setEncoding();

		if (!Request::isAjax() && !isset($_GET['hideHtmlHeader']))
			include 'tpl/tpl.Frontend.header.php';

		Error::getInstance()->header_sent = true;
	}

	/**
	 * Display the HTML-Footer
	 */
	public function displayFooter() {
		if (RUNALYZE_DEBUG && Error::getInstance()->hasErrors()) {
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
