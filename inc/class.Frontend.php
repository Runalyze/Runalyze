<?php
/**
 * This file contains the class::Frontend to create and print the HTML-Page.
 * The class::Frontend is the main class of this project.
 * It will include all needed classes.
 * For using it's enough to include this class.
 */
/**
 * Class: Frontend
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
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
	private $logGetAndPost = false;

	/**
	 * Additional JavaScript-files
	 */
	private $JS_FILES = array();

	/**
	 * Additional CSS-files
	 */
	private $CSS_FILES = array();

	/**
	 * Constructor for Frontend
	 * @param bool $hideHeaderAndFooter optional
	 */
	public function __construct($hideHeaderAndFooter = false) {
		$this->initSystem();

		$this->initRequiredFiles();
		$this->initDebugMode();
		$this->initSessionHandler();
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
		define('FRONTEND_PATH', dirname(__FILE__).'/');

		$this->setAutoloader();
		$this->initErrorHandling();
		$this->initMySql();
		$this->defineConsts();
	}

	/**
	 * Set up Autloader 
	 */
	private function setAutoloader() {
		require_once FRONTEND_PATH.'/system/class.Autoloader.php';
		new Autoloader();
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
	 * Include class::Error and and initialise it
	 */
	private function initErrorHandling() {
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

		Mysql::connect($host, $username, $password, $database);
		unset($host, $username, $password, $database);
	}

	/**
	 * Include class::Mysql and create Session
	 */
	private function initSessionHandler() {
		//SessionHandler::;
		$Session = new SessionHandler();
		if(isset($_POST['user']) && isset($_POST['password'])) {
			//SessionHandler::checkLogin($_POST['user'], $_POST['password']);
			$Session->checkLogin(isset($_POST['user']) && isset($_POST['password']));
		}
	}
	
	/**
	 * Include alle required files
	 */
	private function initRequiredFiles() {
		$this->initImporterExporter();
		$this->initAdditionalFiles();
	}

	/**
	 * Init classes for Importer/Exporter
	 */
	private function initImporterExporter() {
		require_once FRONTEND_PATH.'class.Importer.php';
		require_once FRONTEND_PATH.'class.ImporterFormular.php';

		Importer::registerImporter('TCX', 'ImporterTCX');
		Importer::registerImporter('CSV', 'ImporterCSV');
		Importer::registerImporter('LOGBOOK', 'ImporterLogbook');
		Importer::registerImporter('LOGBOOK3', 'ImporterLogbook3');
		Importer::registerImporter('FITLOG', 'ImporterFITLOG');
	}

	/**
	 * Init all additional files for JS/CSS
	 */
	private function initAdditionalFiles() {
		$this->JS_FILES = array_merge($this->JS_FILES, Ajax::getNeededJSFilesAsArray());
		$this->CSS_FILES = array_merge($this->CSS_FILES, Ajax::getNeededCSSFilesAsArray());

		$Files = glob('plugin/*/*.js');
		if (is_array($Files))
			foreach ($Files as $file)
				$this->JS_FILES[] = $file;

		$Files = glob('plugin/*/*.css');
		if (is_array($Files))
			foreach ($Files as $file)
				$this->CSS_FILES[] = $file;
	}

	/**
	 * Init internal debug-mode. Can be defined in config.php - otherwise is set to false here
	 */
	private function initDebugMode() {
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
	public function setEncoding() {
		header('Content-type: text/html; charset=UTF-8');
		mb_internal_encoding("UTF-8");
	}

	/**
	 * Function to display the HTML-Header
	 */
	public function displayHeader() {
		$this->setEncoding();

		if (!Request::isAjax())
			include 'tpl/tpl.Frontend.header.php';

		Error::getInstance()->header_sent = true;
	}

	/**
	 * Function to display the HTML-Footer
	 */
	public function displayFooter() {
		if (RUNALYZE_DEBUG && Error::getInstance()->hasErrors())
			Error::getInstance()->display();

		if (!Request::isAjax())
			include 'tpl/tpl.Frontend.footer.php';

		Error::getInstance()->footer_sent = true;
	}

	/**
	 * Display the panels for the right side
	 */
	public function displayPanels() {
		$panels = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'plugin` WHERE `type`="panel" AND `active`>0 ORDER BY `order` ASC');
		foreach ($panels as $panel) {
			$Panel = Plugin::getInstanceFor($panel['key']);
			$Panel->display();
		}
	}
}