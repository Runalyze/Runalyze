<?php
use Symfony\Component\Yaml\Yaml;

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
	const REQUIRED_PHP_VERSION = '5.5.9';

	/**
	 * Boolean flag: prefix is set but already used
	 * @var bool
	 */
	protected $isAlreadyInstalled = false;

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
	 * RUNALYZE database prefix
	 */
	protected $databasePrefix = 'runalyze_'; 

	/**
	 * Doctrine object
	 * @var \Doctrine
	 */
	protected $Doctrine = null;
	
	/**
	 * Constructor
	 */
	public function __construct(\Doctrine\Bundle\DoctrineBundle\Registry $doctrine, $prefix = 'runalyze_') {
		$this->Doctrine = $doctrine;
		$this->databasePrefix = $prefix;
		$this->checkIfIsAlreadyInstalled();
	}

	/**
	 * Check if RUNALYZE is already installed?
	 */
	public function checkIfIsAlreadyInstalled() {
		$sql = "SHOW TABLES IN runalyze LIKE '".$this->databasePrefix."training'";
		$em = $this->Doctrine->getManager()->getConnection();
		$stmt = $em->prepare($sql);
		$stmt->execute();
		
		if($stmt->fetch()) {
			$this->isAlreadyInstalled = true;
		}
	}
	
	/**
	 * Is already installed?
	 */
	 public function isAlreadyInstalled() {
	 	return $this->isAlreadyInstalled;
	 }

	/**
	 * Is PHP-version high enough?
	 */
	public function phpVersionIsOkay() {
		return (version_compare(PHP_VERSION, self::REQUIRED_PHP_VERSION) >= 0);
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
	public function installRunalyze() {

		$this->importSqlFile('../inc/install/structure.sql');

		define('FRONTEND_PATH', __DIR__.'/');
		require_once FRONTEND_PATH.'../vendor/autoload.php';
	}

	/**
	 * Import a sql-file to database
	 * @param string $filename
	 * @return array
	 */
	protected function importSqlFile($filename) {
		$Errors  = array();
		$Queries = self::getSqlFileAsArray($filename);

		$em = $this->Doctrine->getManager()->getConnection();

		foreach ($Queries as $query) {
			try {
				$stmt = $em->prepare($query);
				$stmt->execute();
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
	public function getSqlFileAsArray($filename, $removeDelimiter = true) {
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

			$line = str_replace('runalyze_', $this->databasePrefix, $line);

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
