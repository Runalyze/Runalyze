<?php
/**
 * This file contains class::Installer
 * @package Runalyze\Install
 */
/**
 * Installer
 * @author Hannes Christiansen
 * @package Runalyze\Install
 */
class Installer {
	/**
	 * Required PHP-version
	 * @var string
	 */
	static $REQUIRED_PHP_VERSION = '5.3.0';
	
	/**
	* Required MYSQL-version
	* @var string
	*/
	static $REQUIRED_MYSQL_VERSION = '5.0.0';

	/**
	 * Step -1: Runalyze is already installed
	 * @var int
	 */
	static $ALREADY_INSTALLED = -1;

	/**
	 * Step 1: Start of installation
	 * @var int
	 */
	static $START = 1;

	/**
	 * Step 2: Set up configuration
	 * @var int
	 */
	static $SETUP_CONFIG = 2;

	/**
	 * Step 3: Set up database
	 * @var int
	 */
	static $SETUP_DATABASE = 3;

	/**
	 * Step 4: Ready to start
	 * @var int
	 */
	static $READY = 4;

	/**
	 * Number of total steps
	 * @var int
	 */
	static $numberOfSteps = 4;

	/**
	 * Current step of installation
	 * @var int
	 */
	protected $currentStep = 1;

	/**
	 * Ready to move to next step?
	 * @var int
	 */
	protected $readyForNextStep = false;

	/**
	 * Boolean flag: connection is set but incorrect
	 * @var bool
	 */
	protected $connectionIsIncorrect = false;

	/**
	 * Boolean flag: prefix is set but already used
	 * @var bool
	 */
	protected $prefixIsAlreadyUsed = false;

	/**
	 * Boolean flag: writing config-file fails
	 * @var bool
	 */
	protected $cantWriteConfig = false;

	/**
	 * Boolean flag: filling database fails
	 * @var bool
	 */
	protected $cantSetupDatabase = false;

	/**
	 * Array with configuration for mysql-connection
	 * @var array
	 */
	protected $mysqlConfig = array();

	/**
	 * String to write to config file
	 * @var string
	 */
	protected $writeConfigFileString = '';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->definePath();
		$this->findoutCurrentStep();
		$this->loadConfig();
		$this->executeCurrentStep();
		$this->displayCurrentStep();
	}
	
	/**
	* Define const PATH
	*/
	public function definePath() {
		define('PATH', dirname(__FILE__).'/');
	}

	/**
	 * Load configuration file
	 */
	protected function loadConfig() {
		if (file_exists(PATH.'../config.php')) {
			include PATH.'../config.php';

			$this->mysqlConfig = array($host, $username, $password, $database);

			if ($this->currentStep == self::$START) {
				if ($this->databaseIsCorrect())
					$this->currentStep = self::$ALREADY_INSTALLED;
				else
					$this->currentStep = self::$SETUP_DATABASE;
			}
		} else {
			$this->mysqlConfig = array('localhost', '', '', 'runalyze');
			return false;
		}

		return true;
	}

	/**
	 * Findout which is the current step
	 */
	protected function findoutCurrentStep() {
		if (isset($_POST['step']) && is_numeric($_POST['step']) && $_POST['step'] <= self::$numberOfSteps)
			$this->currentStep = $_POST['step'];
		else
			$this->currentStep = self::$START;

		$this->currentStep = self::$READY;
	}

	/**
	 * Execute current step of installation
	 */
	protected function executeCurrentStep() {
		switch ($this->currentStep) {
			case self::$SETUP_CONFIG:
				if (isset($_POST['write_config'])) {
					$this->writeConfigFile();

					if ($this->loadConfig())
						$this->moveToNextStep();
					else
						$this->cantWriteConfig = true;
				} else {
					if (!$this->connectionIsSetAndCorrect())
						$this->connectionIsIncorrect = true;
					elseif (!$this->prefixIsUnused())
						$this->prefixIsAlreadyUsed = true;
					else
						$this->readyForNextStep = true;
				}
				break;

			case self::$SETUP_DATABASE:
				$this->importSqlFiles();

				if ($this->databaseIsCorrect())
					$this->moveToNextStep();
				else
					$this->cantSetupDatabase = true;
				break;
		}
	}

	/**
	 * Execute current step of installation
	 */
	protected function displayCurrentStep() {
		include PATH.'tpl/tpl.Installer.php';
	}

	/**
	 * Move to the next step
	 */
	protected function moveToNextStep() {
		$this->currentStep++;
	}

	/**
	 * Is the connection to the MySql-server setup and correct?
	 */
	protected function connectionIsSetAndCorrect() {
		if (!@mysql_connect($_POST['host'], $_POST['username'], $_POST['password']))
			return false;
		if (!@mysql_select_db($_POST['database']))
			return false;

		return true;
	}

	/**
	 * Is the prefix free for this installation?
	 */
	protected function prefixIsUnused() {
		if (!isset($_POST['prefix']) || strlen($_POST['prefix']) < 2)
			return false;

		return (mysql_num_rows(mysql_query('SHOW TABLES LIKE "'.$_POST['prefix'].'training"')) == 0);
	}

	/**
	 * Is PHP-version high enough?
	 */
	protected function phpVersionIsOkay() {
		return (version_compare(PHP_VERSION, self::$REQUIRED_PHP_VERSION) >= 0);
	}

	/**
	 * Is MySQL-version high enough?
	 */
	protected function mysqlVersionIsOkay() {
		return (version_compare($this->getMysqlVersion(), self::$REQUIRED_MYSQL_VERSION) >= 0);
	}

	/**
	 * Get current MySQL-version
	 */
	protected function getMysqlVersion() {
		return @mysql_get_server_info();
	}

	/**
	 * Write config-variables to file
	 */
	protected function writeConfigFile() {
		$config['host']      = $_POST['host'];
		$config['database']  = $_POST['database'];
		$config['username']  = $_POST['username'];
		$config['password']  = $_POST['password'];
		$config['prefix']    = $_POST['prefix'];
		$config['debug']     = isset($_POST['debug']) ? 'true' : 'false';
		$config['login']     = isset($_POST['login']) ? 'true' : 'false';
		$config['garminkey'] = $_POST['garminkey'];

		$file_string = @file_get_contents(PATH.'install/config.php');

		if ($file_string === false)
			return;

		$file_string = preg_replace('/{config::([^}]*)}/ie', 'isset($config["$1"])?$config["$1"]:"$0"', $file_string);

		@file_put_contents(PATH.'../config.php', $file_string);

		$this->writeConfigFileString = $file_string;
	}

	/**
	 * Replace new lines with html-breaks
	 * @param string $filename
	 * @return string
	 */
	protected function getSqlContentForFrontend($filename) {
		return implode("\n", $this->getSqlFileAsArray($filename));
	}

	/**
	 * Import all needed sql-dumps to database
	 */
	protected function importSqlFiles() {
		@mysql_connect($this->mysqlConfig[0], $this->mysqlConfig[1], $this->mysqlConfig[2]);
		@mysql_select_db($this->mysqlConfig[3]);

		self::importSqlFile(PATH.'install/structure.sql');
		self::importSqlFile(PATH.'install/runalyze_empty.sql');
	}

	/**
	 * Check if the database is filled
	 * @return bool
	 */
	protected function databaseIsCorrect() {
		@mysql_connect($this->mysqlConfig[0], $this->mysqlConfig[1], $this->mysqlConfig[2]);
		@mysql_select_db($this->mysqlConfig[3]);

		return (@mysql_num_rows(@mysql_query('SHOW TABLES LIKE "'.PREFIX.'training"')) > 0);
	}

	/**
	 * Import a sql-file to database
	 * @param string $filename
	 * @return array
	 */
	static public function importSqlFile($filename) {
		$Errors  = array();
		$Queries = self::getSqlFileAsArray($filename);

		foreach ($Queries as $Query) {
			mysql_query($Query);

			if (mysql_errno())
				$Errors[] = mysql_error();
		}

		return $Errors;
	}

	/**
	 * Import a sql-file
	 * @param string $filename relative to PATH!
	 * @return array
	 */
	static public function getSqlFileAsArray($filename) {
		$MRK = array('delimiter', 'USE', 'SET', 'LOCK', 'SHOW', 'DROP', 'GRANT', 'ALTER', 'UNLOCK', 'CREATE', 'INSERT', 'UPDATE', 'DELETE', 'REVOKE', 'REPLACE', 'RENAME', 'TRUNCATE');
		$SQL = @file($filename);
		$query  = '';
		$array = array();

		foreach ($SQL as $line) {
			$line = trim($line);

			if (defined('PREFIX'))
				$line = str_replace('runalyze_', PREFIX, $line);
			
			if (isset($mysqlConfig[3]) && !isset($_POST['database'])) {
				$line = str_replace('DATABASE runalyze', $mysqlConfig[3], $line);
				$line = str_replace('DATABASE `runalyze`', $mysqlConfig[3], $line);
			}

			$AA = explode(' ', $line);
			if (in_array(strtoupper($AA[0]), $MRK)) {
				$query = $line;
			} elseif (strlen($query) > 1) {
				$query .= " ".$line;
			}

			$x = strlen($query) - 1;
			if (mb_substr($query,$x) == ';') {
				$array[] = $query;
				$query = '';
			}
		}

		return $array;
	}
}