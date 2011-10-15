<?php
/**
 * This file contains the class::Installer for installing Runalyze
 */
/**
 * Class: Installer
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Error
 * @uses class::Mysql
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
	private $currentStep = 1;

	/**
	 * Ready to move to next step?
	 * @var int
	 */
	private $readyForNextStep = false;

	/**
	 * Boolean flag: connection is set but incorrect
	 * @var bool
	 */
	private $connectionIsIncorrect = false;

	/**
	 * Boolean flag: prefix is set but already used
	 * @var bool
	 */
	private $prefixIsAlreadyUsed = false;

	/**
	 * Array with configuration for mysql-connection
	 * @var array
	 */
	private $mysqlConfig = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->findoutCurrentStep();
		$this->loadConfig();
		$this->executeCurrentStep();
		$this->displayCurrentStep();
	}

	/**
	 * Load configuration file
	 */
	private function loadConfig() {
		if (file_exists('config.php')) {
			if ($this->currentStep == self::$START)
				$this->currentStep = self::$ALREADY_INSTALLED;

			include 'config.php';
		}

		$this->mysqlConfig = array($host, $database, $username, $password);
	}

	/**
	 * Findout which is the current step
	 */
	private function findoutCurrentStep() {
		if (isset($_POST['step']) && is_numeric($_POST['step']) && $_POST['step'] <= self::$numberOfSteps)
			$this->currentStep = $_POST['step'];
		else
			$this->currentStep = self::$START;
	}

	/**
	 * Execute current step of installation
	 */
	private function executeCurrentStep() {
		switch ($this->currentStep) {
			case self::$SETUP_CONFIG:
				if (isset($_POST['write_config'])) {
					$this->writeConfigFile();
					$this->moveToNextStep();
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
				$this->moveToNextStep();
				break;
		}
	}

	/**
	 * Execute current step of installation
	 */
	private function displayCurrentStep() {
		include 'tpl/tpl.Installer.php';
	}

	/**
	 * Move to the next step
	 */
	private function moveToNextStep() {
		$this->currentStep++;
	}

	/**
	 * Is the connection to the MySql-server setup and correct?
	 */
	private function connectionIsSetAndCorrect() {
		if (!@mysql_connect($_POST['host'], $_POST['username'], $_POST['password']))
			return false;
		if (!@mysql_select_db($_POST['database']))
			return false;

		return true;
	}

	/**
	 * Is the prefix free for this installation?
	 */
	private function prefixIsUnused() {
		if (strlen($_POST['prefix']) < 2)
			return false;

		return (mysql_num_rows(mysql_query('SHOW TABLES LIKE "'.$_POST['prefix'].'training"')) == 0);
	}

	/**
	 * Is PHP-version high enough?
	 */
	private function phpVersionIsOkay() {
		return (version_compare(PHP_VERSION, self::$REQUIRED_PHP_VERSION) >= 0);
	}

	/**
	 * Is MySQL-version high enough?
	 */
	private function mysqlVersionIsOkay() {
		return (version_compare($this->getMysqlVersion(), self::$REQUIRED_MYSQL_VERSION) >= 0);
	}

	/**
	 * Get current MySQL-version
	 */
	private function getMysqlVersion() {
		return @mysql_get_server_info();
	}

	/**
	 * Write config-variables to file
	 */
	private function writeConfigFile() {
		$config['host']          = $_POST['host'];
		$config['database']      = $_POST['database'];
		$config['username']      = $_POST['username'];
		$config['password']      = $_POST['password'];
		$config['prefix']        = $_POST['prefix'];
		$config['debug_slashes'] = isset($_POST['debug']) ? '' : '//';

		$file_string = file_get_contents('inc/install/config.php');
		$file_string = preg_replace('/{config::([^}]*)}/ie', 'isset($config["$1"])?$config["$1"]:"$0"', $file_string);

		file_put_contents('config.php', $file_string);
	}

	/**
	 * Replace new lines with html-breaks
	 * @param string $filename
	 * @return string
	 */
	private function getSqlContentForFrontend($filename) {
		return implode('<br />', $this->getSqlFileAsArray($filename));
	}

	/**
	 * Import all needed sql-dumps to database
	 */
	private function importSqlFiles() {
		@mysql_connect($this->mysqlConfig[0], $this->mysqlConfig[1], $this->mysqlConfig[2]);
		@mysql_select_db($this->mysqlConfig[3]);

		$this->importSqlFile('inc/install/structure.sql');
		$this->importSqlFile('inc/install/runalyze_empty.sql');
	}

	/**
	 * Import a sql-file to database
	 * @param unknown_type $filename
	 */
	private function importSqlFile($filename) {
		$Queries = $this->getSqlFileAsArray($filename);
		foreach ($Queries as $Query) {
			mysql_query($Query);
		}
	}

	/**
	 * Import a sql-file
	 * @param string $filename
	 * @return array
	 */
	private function getSqlFileAsArray($filename) {
		$MRK = array('USE', 'SET', 'LOCK', 'SHOW', 'DROP', 'GRANT', 'ALTER', 'UNLOCK', 'CREATE', 'INSERT', 'UPDATE', 'DELETE', 'REVOKE', 'REPLACE');
		$SQL = file($filename);
		$query  = '';
		$array = array();

		foreach($SQL as $line) {
			$line = trim($line);

			if (defined('PREFIX'))
				$line = str_replace('runalyze_', PREFIX, $line);

			$AA = explode(' ', $line);
			if (in_Array(strtoupper($AA[0]), $MRK)) {
				$query = $line;
			} elseif (strlen($query) > 1) {
				$query .= " ".$line;
			}

			$x = strlen($query) - 1;
			if (substr($query,$x) == ';') {
				$array[] = $query;
				$query = '';
			}
		}

		return $array;
	}
}
?>