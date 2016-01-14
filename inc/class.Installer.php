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
	const REQUIRED_PHP_VERSION = '5.4.0';

	/**
	* Required MYSQL-version
	* @var string
	*/
	const REQUIRED_MYSQL_VERSION = '5.0.0';

	/**
	 * Step -1: Runalyze is already installed
	 * @var int
	 */
	const ALREADY_INSTALLED = -1;

	/**
	 * Step 1: Start of installation
	 * @var int
	 */
	const START = 1;

	/**
	 * Step 2: Set up configuration
	 * @var int
	 */
	const SETUP_CONFIG = 2;

	/**
	 * Step 3: Set up database
	 * @var int
	 */
	const SETUP_DATABASE = 3;

	/**
	 * Step 4: Ready to start
	 * @var int
	 */
	const READY = 4;

	/**
	 * Number of total steps
	 * @var int
	 */
	const NUMBER_OF_STEPS = 4;

	/**
	 * Current step of installation
	 * @var int
	 */
	protected $currentStep = 1;

	/**
	 * Ready to move to next step?
	 * @var bool
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
	 * PDO object
	 * @var \PDO
	 */
	protected $PDO = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->definePath();
		$this->initLanguage();
		$this->loadConsts();
	}

	/**
	 * Display
	 */
	public function display() {
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
		define('FRONTEND_PATH', dirname(__FILE__).'/');
	}

	/**
	 * Setup Language
	 */
	protected function initLanguage() {
		require_once PATH.'system/class.Language.php';
		new Language();
	}

	/**
	 * Load consts
	 */
	protected function loadConsts() {
		require_once PATH.'system/define.consts.php';
	}

	/**
	 * Load configuration file
	 */
	protected function loadConfig() {
		if (defined('PREFIX')) {
			return true;
		}

		if (file_exists(PATH.'../data/config.php')) {
			include PATH.'../data/config.php';

			$this->mysqlConfig = array($host, $username, $password, $database, $port);

			if ($this->currentStep == self::START) {
				if ($this->databaseIsCorrect())
					$this->currentStep = self::ALREADY_INSTALLED;
				else
					$this->currentStep = self::SETUP_DATABASE;
			}
		} else {
			$this->mysqlConfig = array('localhost', '', '', 'runalyze', 3306);
			return false;
		}

		return true;
	}

	/**
	 * Findout which is the current step
	 */
	protected function findoutCurrentStep() {
		if (isset($_POST['step']) && is_numeric($_POST['step']) && $_POST['step'] <= self::NUMBER_OF_STEPS)
			$this->currentStep = $_POST['step'];
		else
			$this->currentStep = self::START;
	}

	/**
	 * Execute current step of installation
	 */
	protected function executeCurrentStep() {
		switch ($this->currentStep) {
			case self::SETUP_CONFIG:
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

			case self::SETUP_DATABASE:
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
		if (!isset($_POST['database']))
			return false;

		try {
			$this->connectToDatabase($_POST['database'], $_POST['host'], $_POST['port'],  $_POST['username'], $_POST['password']);

			return true;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Connect to database
	 * @param string $db
	 * @param string $host
	 * @param int $port
	 * @param string $user
	 * @param string $pw
	 */
	protected function connectToDatabase($db, $host, $port, $user, $pw) {
		$this->PDO = new PDO('mysql:dbname='.$db.';host='.$host.';port='.$port.';charset=utf8', $user, $pw);
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->PDO->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

		if (version_compare(PHP_VERSION, '5.3.6', '<')) {
			$this->PDO->exec("SET NAMES 'utf8'");
		}
	}

	/**
	 * Is the prefix free for this installation?
	 */
	protected function prefixIsUnused() {
		if (!isset($_POST['prefix']) || strlen($_POST['prefix']) < 2)
			return false;

		return !$this->PDO->query('SHOW TABLES LIKE "'.$_POST['prefix'].'training"')->fetch();
	}

	/**
	 * Is PHP-version high enough?
	 */
	protected function phpVersionIsOkay() {
		return (version_compare(PHP_VERSION, self::REQUIRED_PHP_VERSION) >= 0);
	}

	/**
	 * Is MySQL-version high enough?
	 */
	protected function mysqlVersionIsOkay() {
		return (version_compare($this->getMysqlVersion(), self::REQUIRED_MYSQL_VERSION) >= 0);
	}

	/**
	 * Get current MySQL-version
	 */
	protected function getMysqlVersion() {
		if ($this->PDO == null) {
			if ($this->mysqlConfig[1] == '') {
				return '';
			}

			$this->connectToDatabase($this->mysqlConfig[3], $this->mysqlConfig[0], $this->mysqlConfig[4], $this->mysqlConfig[1], $this->mysqlConfig[2]);
		}

		return $this->PDO->getAttribute(PDO::ATTR_SERVER_VERSION);
	}

	/**
	 * Write config-variables to file
	 */
	protected function writeConfigFile() {
		$config = array();
		$config['host']      = $_POST['host'];
		$config['port']      = $_POST['port'];
		$config['database']  = $_POST['database'];
		$config['username']  = $_POST['username'];
		$config['password']  = $_POST['password'];
		$config['prefix']    = $_POST['prefix'];
		$config['debug']     = isset($_POST['debug']) ? 'true' : 'false';
		$config['garminkey'] = $_POST['garminkey'];

		$file_string = @file_get_contents(PATH.'install/config.php');

		if ($file_string === false)
			return;

		$file_string = preg_replace_callback('/{config::([^}]*)}/i', function($result) use ($config) {
			return (isset($config[$result[1]])) ? $config[$result[1]] : $result[0];
		}, $file_string);

		@file_put_contents(PATH.'../data/config.php', $file_string);

		$this->writeConfigFileString = $file_string;
	}

	/**
	 * Replace new lines with html-breaks
	 * @param string $filename
	 * @return string
	 */
	protected function getSqlContentForFrontend($filename) {
		return implode("\n", $this->getSqlFileAsArray($filename, false));
	}

	/**
	 * Import all needed sql-dumps to database
	 */
	protected function importSqlFiles() {
		$this->connectToDatabase($this->mysqlConfig[3], $this->mysqlConfig[0], $this->mysqlConfig[4], $this->mysqlConfig[1], $this->mysqlConfig[2]);

		$this->importSqlFile('inc/install/structure.sql');

		define('FRONTEND_PATH', __DIR__.'/');
		require_once FRONTEND_PATH.'/system/class.Autoloader.php';
		new Autoloader();

		DB::connect($this->mysqlConfig[0], $this->mysqlConfig[4], $this->mysqlConfig[1], $this->mysqlConfig[2], $this->mysqlConfig[3]);
	}

	/**
	 * Check if the database is filled
	 * @return bool
	 */
	protected function databaseIsCorrect() {
		$this->connectToDatabase($this->mysqlConfig[3], $this->mysqlConfig[0], $this->mysqlConfig[4], $this->mysqlConfig[1], $this->mysqlConfig[2]);

		$statement = $this->PDO->prepare('SHOW TABLES LIKE "'.PREFIX.'training"');
		$statement->execute();

		return count($statement->fetch()) > 0;
	}

	/**
	 * Import a sql-file to database
	 * @param string $filename
	 * @return array
	 */
	protected function importSqlFile($filename) {
		$Errors  = array();
		$Queries = self::getSqlFileAsArray($filename);

		foreach ($Queries as $query) {
			try {
				$this->PDO->query($query);
			} catch (PDOException $e) {
				$Errors[] = $e->getMessage();
			}
		}

		return $Errors;
	}

	/**
	 * Import a sql-file
	 * @param string $filename relative to PATH!
	 * @return array
	 */
	public static function getSqlFileAsArray($filename, $removeDelimiter = true) {
		$MRK = array('DELIMITER', 'USE', 'SET', 'LOCK', 'SHOW', 'DROP', 'GRANT', 'ALTER', 'UNLOCK', 'CREATE', 'INSERT', 'UPDATE', 'DELETE', 'REVOKE', 'REPLACE', 'RENAME', 'TRUNCATE');
		$SQL = @file($filename);
		$query  = '';
		$array = array();
		$inDelimiter = false;

		if (!is_array($SQL)) {
			$SQL = array();
		}

		foreach ($SQL as $line) {
			$line = trim($line);

			if (defined('PREFIX'))
				$line = str_replace('runalyze_', PREFIX, $line);

			if (isset($mysqlConfig[3]) && !isset($_POST['database'])) {
				$line = str_replace('DATABASE runalyze', $mysqlConfig[3], $line);
				$line = str_replace('DATABASE `runalyze`', $mysqlConfig[3], $line);
			}

			if ($inDelimiter) {
			    if (mb_substr($line, 0, 9) == 'DELIMITER') {
				$inDelimiter = false;
				$query .= $removeDelimiter ? '' : ' '.$line;
				$array[] = $query;
			    } elseif (trim($line) != '//') {
				$query .= ' '.$line;
			    }
			} else {
			    $AA = explode(' ', $line);
			    if (in_array(strtoupper($AA[0]), $MRK)) {
				if ($AA[0] == 'DELIMITER') {
				    $inDelimiter = true;
				    $query = $removeDelimiter ? '' : $line;
				} else {
				    $query = $line;
				}
			    } elseif (strlen($query) > 1) {
					$query .= " ".$line;
				}

				$x = strlen($query) - 1;
				if (mb_substr($query,$x) == ';') {
					$array[] = $query;
					$query = '';
				}
			}
		}

		return $array;
	}
}
