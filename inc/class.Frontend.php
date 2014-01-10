<?php
/**
 * This file contains the class::Frontend to create and print the HTML-Page.
 * @package Runalyze\Frontend
 */
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
	public static $HELP_URL = 'inc/tpl/tpl.help.html';

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
                $this->initErrorHandling();
		$this->initMySql();
		$this->initDebugMode();
		$this->initSessionAccountHandler();
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
            if(!empty($_GET['lang']))
                $language = $_GET['lang'];
            else
                $language = 'en';
            $locale_dir = './inc/locale';
            putenv("LANG=$language"); 
            setlocale(LC_ALL, $language);
            $domain = 'runalyze';
            bindtextdomain('runalyze', $locale_dir); 
            textdomain('runalyze');
            
        }
	/**
	 * Init constants
	 */
	private function defineConsts() {
		require_once FRONTEND_PATH.'system/define.consts.php';
		require_once FRONTEND_PATH.'system/register.consts.php';

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
		Error::init(Request::Uri());

		if ($this->logGetAndPost) {
			if (!empty($_POST))
				Error::getInstance()->addDebug('POST-Data: '.print_r($_POST, true));
			if (!empty($_GET))
				Error::getInstance()->addDebug('GET-Data: '.print_r($_GET, true));
		}
	}

	/**
	 * Include class::Mysql and connect to database
	 */
	private function initMySql() {
		require_once FRONTEND_PATH.'../config.php';

		$this->adminPassAsMD5 = md5($password);

		Mysql::connect($host, $username, $password, $database);
		unset($host, $username, $password, $database);
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
		$Session = new SessionAccountHandler();

		if (isset($_POST['user']) && isset($_POST['password']))
			$Session->tryToLogin($_POST['user'], $_POST['password']);
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
		if (RUNALYZE_DEBUG && Error::getInstance()->hasErrors())
			Error::getInstance()->display();

		if (!Request::isAjax() && !isset($_GET['hideHtmlHeader']))
			include 'tpl/tpl.Frontend.footer.php';

		Error::getInstance()->footer_sent = true;
	}

	/**
	 * Display panels
	 */
	public function displayPanels() {
		$panels = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'plugin` WHERE `type`="panel" AND `active`>0 ORDER BY `order` ASC');
		foreach ($panels as $panel) {
			$Panel = Plugin::getInstanceFor($panel['key']);
			$Panel->display();
		}
	}

	/**
	 * Test a plot
	 * 
	 * Will be displayed instead of the DataBrowser - Only for testing purposes!
	 * @param string $includePath 
	 * @param string $name
	 * @param int $width
	 * @param int $height
	 */
	public function testPlot($includePath, $name, $width, $height) {
		echo '<div id="container"><div id="main"><div id="dataPanel" class="panel c">';

		echo Plot::getDivFor($name, $width, $height);
		include FRONTEND_PATH.$includePath;

		echo '</div></div></div>';

		exit();
	}
}