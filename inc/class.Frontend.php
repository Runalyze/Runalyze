<?php
/**
 * This file contains the class::Frontend to create and print the HTML-Page.
 * The class::Frontend is the main class of this project.
 * It will include all needed classes.
 * For using it's enough to include this class.
 */
/**
 * Class: Frontend
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
	protected $logGetAndPost = false;

	/**
	 * Admin password as md5
	 * @var string
	 */
	protected $adminPassAsMD5 = '';

	/**
	 * Constructor for Frontend
	 * @param bool $hideHeaderAndFooter optional
	 */
	public function __construct($hideHeaderAndFooter = false) {
		$this->initSystem();

		$this->initRequiredFiles();
		$this->initDebugMode();
		$this->initSessionAccountHandler();
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

		$this->setAutoloader();
		$this->initErrorHandling();
		$this->initMySql();
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
	 * Include alle required files
	 */
	protected function initRequiredFiles() {
		$this->initImporterExporter();
	}

	/**
	 * Init classes for Importer/Exporter
	 */
	protected function initImporterExporter() {
		require_once FRONTEND_PATH.'import/class.Importer.php';
		require_once FRONTEND_PATH.'import/class.ImporterFormular.php';

		Importer::registerImporter('TCX', 'ImporterTCX');
		Importer::registerImporter('GPX', 'ImporterGPX');
		Importer::registerImporter('SLF', 'ImporterSLF');
		Importer::registerImporter('FITLOG', 'ImporterFITLOG');
		Importer::registerImporter('LOGBOOK', 'ImporterLogbook');
		Importer::registerImporter('LOGBOOK3', 'ImporterLogbook3');
		Importer::registerImporter('CSV', 'ImporterCSV');
		Importer::registerImporter('PWX', 'ImporterPWX');
		Importer::registerImporter('XML', 'ImporterXML');

		Exporter::registerExporter('TCX', 'ExporterTCX');
		Exporter::registerExporter('GPX', 'ExporterGPX');
		Exporter::registerExporter('KML', 'ExporterKML');
		Exporter::registerExporter('FITLOG', 'ExporterFITLOG');

		// TODO: add option
		if (!System::isAtLocalhost()) {
			Exporter::registerExporter('Twitter', 'ExporterTwitter');
			Exporter::registerExporter('Facebook', 'ExporterFacebook');
			Exporter::registerExporter('Google', 'ExporterGoogle');
		}

		Exporter::registerExporter('HTML', 'ExporterHTML');
		Exporter::registerExporter('IFrame', 'ExporterIFrame');
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
	 * Function to display the HTML-Header
	 */
	public function displayHeader() {
		$this->setEncoding();

		if (!Request::isAjax() && !isset($_GET['hideHtmlHeader']))
			include 'tpl/tpl.Frontend.header.php';

		Error::getInstance()->header_sent = true;
	}

	/**
	 * Function to display the HTML-Footer
	 */
	public function displayFooter() {
		if (RUNALYZE_DEBUG && Error::getInstance()->hasErrors())
			Error::getInstance()->display();

		if (!Request::isAjax() && !isset($_GET['hideHtmlHeader']))
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

	/**
	 * Test a plot - Will be displayed instead of the DataBrowser - Only for testing purposes!
	 * @param string $includePath 
	 */
	public function testPlot($includePath, $name, $width, $height) {
		echo '<div id="container"><div id="main"><div id="dataPanel" class="panel c">';

		echo Plot::getDivFor($name, $width, $height);
		include FRONTEND_PATH.$includePath;

		echo '</div></div></div>';

		exit();
	}
}