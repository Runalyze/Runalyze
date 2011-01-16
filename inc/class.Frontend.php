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
 * @version 1.0
 * @uses class::Mysql
 * @uses class::Error
 * @uses class::Ajax
 * @uses class::Panel // Will be included by class::Plugin later?
 * @uses class::Stat // Will be included by class::Plugin later?
 * @uses '../config/dataset.php' // Will be a class later
 * @uses '../config/functions.php' // TODO functions.php Must be a helper-class later
 * @uses '../config/globals.php' // Has to be done on another way
 * // @uses class::Plugin
 * // @uses class::Training
 * // @uses class::Parser // Will be included by class::Training later
 * // @uses class::Draw // Including by other classes?
 *
 * Last modified 2010/08/13 22:00 by Hannes Christiansen
 */
class Frontend {
	public $global;
	private $ajax_request,
		$file;

	function __construct($ajax_request = false, $file = __FILE__) {
		global $mysql, $error, $global;

		header('Content-type: text/html; charset=ISO-8859-1');

		$this->initConsts();
		$this->initErrorHandling();
		$this->initMySql();
		$this->initConfigConsts();

		require_once(FRONTEND_PATH.'class.Ajax.php');
		require_once(FRONTEND_PATH.'class.Panel.php'); // Will be included by class::Plugin later?
		require_once(FRONTEND_PATH.'class.Stat.php'); // Will be included by class::Plugin later?
		require_once(FRONTEND_PATH.'class.Helper.php'); // Will be included by class::Plugin later?
		require_once(FRONTEND_PATH.'..\\config\\dataset.php'); // Will be a class later
		require_once(FRONTEND_PATH.'..\\config\\globals.php'); // Has to be done on another way
		require_once(FRONTEND_PATH.'..\\config\\functions.php'); // TODO functions.php Must be a helper-class later
		$error->add('TODO','Following classes have to be implementated: Plugin, Training, Parser, Draw');
		// require_once(FRONTEND_PATH.'class.Plugin.php');
		require_once(FRONTEND_PATH.'class.Training.php');
		// require_once(FRONTEND_PATH.'class.Parser.php'); // Will be included by class::Training later
		// require_once(FRONTEND_PATH.'class.Draw.php'); // Including by other classes?

		if (!is_bool($ajax_request)) {
			$error->add('WARNING','First argument for class::Frontend__construct() is expected to be boolean.');
			$this->ajax_request = true;
		} else {
			$this->ajax_request = $ajax_request;
		}

		$this->file = $file;
		$this->global = $global;
	}

	/**
	 * Destructer, closes mysql-connection and prints error-log if set
	 */
	function __destruct() {
		unset($this->mysql, $this->error);
	}

	/**
	 * Calls the destructer
	 */
	function close() {
		$this->__destruct();
	}

	/**
	 * Init constants
	 */
	function initConsts() {
		define('FRONTEND_PATH', dirname(__FILE__).'\\');
		define('LTB_VERSION', '0.5');
		define('LTB_DEBUG', true);
		define('INFINITY', PHP_INT_MAX);
		define('DAY_IN_S', 86400);
		define('YEAR', date("Y"));
		define('CUT_LENGTH', 29);
		define('NL', "\n");
	}

	/**
	 * Include class::Error and and init $error
	 */
	function initErrorHandling() {
		global $error;

		require_once(FRONTEND_PATH.'class.Error.php');
		$error = new Error();
	}

	/**
	 * Include class::Mysql and connect to database
	 */
	function initMySql() {
		global $mysql, $error;

		require_once(FRONTEND_PATH.'class.Mysql.php');
		require_once(FRONTEND_PATH.'config.inc.php');
		$mysql = new Mysql($host, $username, $password, $database);
		unset($host, $username, $password, $database);
	}

	/**
	 * Define all CONFIG_CONSTS
	 */
	function initConfigConsts() {
		global $mysql, $error;

		$config = $mysql->fetch('SELECT * FROM `ltb_config` LIMIT 1');
		foreach ($config as $key => $value)
			define('CONFIG_'.strtoupper($key), $value);
		unset($config);
	}

	/**
	 * Function to display the HTML-Header
	 */
	function displayHeader() {
		global $mysql, $error;

		if (!$this->ajax_request)
			include('tpl/tpl.Frontend.header.php');
	}

	/**
	 * Function to display the HTML-Footer
	 */
	function displayFooter() {
		global $mysql, $error;

		if (LTB_DEBUG)
			include('tpl/tpl.Frontend.debug.php');

		if (!$this->ajax_request)
			include('tpl/tpl.Frontend.footer.php');
	}

	/**
	 * Display the panels for the right side
	 */
	function displayPanels() {
		global $mysql, $error;

		$panels = $mysql->fetch('SELECT * FROM `ltb_plugin` WHERE `type`="panel" AND `active`>0 ORDER BY `order` ASC');
		foreach($panels as $i => $panel) {
			$panel = new Panel($panel['id']);
			$panel->display();
		}
	}
}
?>