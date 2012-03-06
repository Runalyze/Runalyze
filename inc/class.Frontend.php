<?php
/**
 * This file contains the class::Frontend to create and print the HTML-Page.
 * The class::Frontend is the main class of this project.
 * It will include all needed classes.
 * For using it's enough to include this class.
 */

date_default_timezone_set('Europe/Berlin');

/**
 * Class: Frontend
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 */
class Frontend {
	/**
	 * Boolean flag if it was an Ajax-request
	 * @var bool
	 */
	private $ajax_request;

	/**
	 * Called filename
	 * @var string
	 */
	private $file;

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
	 * Constructor for frontend
	 * @param bool $ajax_request Is the call an Ajax-request?
	 * @param string $file Current filename
	 */
	public function __construct($ajax_request = false, $file = __FILE__) {
		$this->file = $file;
		$this->ajax_request = $ajax_request;

		$this->initConsts();
		$this->initVars();
		$this->initErrorHandling();
		$this->initMySql();
		$this->initConfigConsts();
		$this->initRequiredFiles();
		$this->initDebugMode();
	}

	/**
	 * Destructer, closes mysql-connection and prints error-log if set (hopefully without another call?)
	 */
	public function __destruct() {}

	/**
	 * Calls the destructer
	 */
	public function close() {
		$this->__destruct();
	}

	/**
	 * Init constants
	 */
	private function initConsts() {
		define('FRONTEND_PATH', dirname(__FILE__).'/');
		define('RUNALYZE_VERSION', '1.0');
		define('INFINITY', PHP_INT_MAX);
		define('DAY_IN_S', 86400);
		define('YEAR', date("Y"));
		define('CUT_LENGTH', 29);
	}

	/**
	 * Init class-variables
	 */
	private function initVars() {
		if (!is_bool($this->ajax_request)) {
			Error::getInstance()->add('WARNING',' First argument for class::Frontend__construct() is expected to be boolean.');
			$this->ajax_request = true;
		}
	}

	/**
	 * Include class::Error and and initialise it
	 */
	private function initErrorHandling() {
		require_once FRONTEND_PATH.'class.Error.php';
		Error::init($this->file);

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
		require_once FRONTEND_PATH.'class.Mysql.php';
		require_once FRONTEND_PATH.'../config.php';

		Mysql::connect($host, $username, $password, $database);
		unset($host, $username, $password, $database);
	}

	/**
	 * Define all CONF_CONSTS
	 */
	private function initConfigConsts() {
		require_once FRONTEND_PATH.'class.Config.php';

		Config::register('Allgemein', 'GENDER', 'select', array('m' => true, 'f' => false), 'Geschlecht', array('m&auml;nnlich', 'weiblich'));
		Config::register('Allgemein', 'PULS_MODE', 'select', array('bpm' => false, 'hfmax' => true), 'Pulsanzeige', array('absoluter Wert', '&#37; HFmax'));
		Config::register('Allgemein', 'USE_PULS', 'bool', true, 'Pulsdaten speichern');
		Config::register('Allgemein', 'USE_WETTER', 'bool', true, 'Wetter speichern');
		Config::register('Allgemein', 'PLZ', 'int', 0, 'f&uuml;r Wetter-Daten: PLZ');
		Config::register('Rechenspiele', 'RECHENSPIELE', 'bool', true, 'Rechenspiele aktivieren');

		$this->initDesignConsts();
	}

	/**
	 * Define all CONF_CONSTS
	 */
	private function initDesignConsts() {
		Config::register('Design', 'DESIGN_BG_FILE', 'selectfile', 'img/backgrounds/Default.jpg', 'Hintergrundbild (Neuladen notwendig, eigene Bilder in img/backgrounds/)', array('img/backgrounds/'));
		Config::register('Design', 'DESIGN_BG_FIX_AND_STRETCH', 'bool', true, 'Hintergrundbild skalieren (Neuladen notwendig)');
	}

	/**
	 * Include alle required files
	 */
	private function initRequiredFiles() {
		require_once FRONTEND_PATH.'class.Training.php';
		require_once FRONTEND_PATH.'class.TrainingDisplay.php';
		require_once FRONTEND_PATH.'class.Ajax.php';
		require_once FRONTEND_PATH.'class.HTML.php';
		require_once FRONTEND_PATH.'class.Helper.php';
		require_once FRONTEND_PATH.'class.Icon.php';
		require_once FRONTEND_PATH.'class.DataBrowser.php';
		require_once FRONTEND_PATH.'class.Dataset.php';
		require_once FRONTEND_PATH.'class.Plugin.php';
		require_once FRONTEND_PATH.'class.PluginPanel.php';
		require_once FRONTEND_PATH.'class.PluginStat.php';
		require_once FRONTEND_PATH.'class.Plot.php';
		require_once FRONTEND_PATH.'class.PluginTool.php';
		require_once FRONTEND_PATH.'class.Draw.php';
		require_once FRONTEND_PATH.'class.Clothes.php';
		require_once FRONTEND_PATH.'class.Shoe.php';
		require_once FRONTEND_PATH.'class.Sport.php';
		require_once FRONTEND_PATH.'class.Type.php';
		require_once FRONTEND_PATH.'class.Weather.php';
		require_once FRONTEND_PATH.'class.GpsData.php';
		require_once FRONTEND_PATH.'class.Gmap.php';
		require_once FRONTEND_PATH.'class.Editor.php';
		require_once FRONTEND_PATH.'class.Validator.php';

		require_once FRONTEND_PATH.'system/class.Request.php';
		require_once FRONTEND_PATH.'system/class.DatabaseScheme.php';
		require_once FRONTEND_PATH.'system/class.DatabaseSchemePool.php';

		require_once FRONTEND_PATH.'training/class.DataObject.php';
		require_once FRONTEND_PATH.'training/class.UserData.php';

		require_once FRONTEND_PATH.'html/class.HtmlTag.php';
		require_once FRONTEND_PATH.'html/class.FormularValueParser.php';
		require_once FRONTEND_PATH.'html/class.Formular.php';
		require_once FRONTEND_PATH.'html/class.FormularFieldset.php';
		require_once FRONTEND_PATH.'html/class.FormularField.php';
		require_once FRONTEND_PATH.'html/class.FormularInput.php';
		require_once FRONTEND_PATH.'html/class.StandardFormular.php';

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

		$Files = glob('plugin/*/*.js');
		if (is_array($Files))
			foreach ($Files as $file)
				$this->JS_FILES[] = $file;

		$Files = glob('plugin/*/*.js');
		if (is_array($Files))
			foreach ($Files as $file)
				$this->CSS_FILES[] = $file;

		$Files = glob('plugin/*/*.js');
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
	 * Function to display the HTML-Header
	 */
	public function displayHeader() {
		header('Content-type: text/html; charset=ISO-8859-1');

		if (!$this->ajax_request)
			include 'tpl/tpl.Frontend.header.php';

		Error::getInstance()->header_sent = true;
	}

	/**
	 * Function to display the HTML-Footer
	 */
	public function displayFooter() {
		if (RUNALYZE_DEBUG && Error::getInstance()->hasErrors())
			include 'tpl/tpl.Frontend.debug.php';

		if (!$this->ajax_request)
			include 'tpl/tpl.Frontend.footer.php';

		Error::getInstance()->footer_sent = true;
	}

	/**
	 * Display the panels for the right side
	 */
	public function displayPanels() {
		$panels = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'plugin` WHERE `type`="panel" AND `active`>0 ORDER BY `order` ASC');
		foreach ($panels as $i => $panel) {
			$Panel = Plugin::getInstanceFor($panel['key']);
			$Panel->display();
		}
	}

	/**
	 * Get link to the help window
	 * @return string
	 */
	static public function getHelpOverlayLink() {
		return Ajax::window('<a class="left" href="inc/tpl/tpl.help.html">'.Icon::get(Icon::$CONF_HELP, '', '', 'Hilfe').'</a>');
	}
}
?>