<?php
/**
 * This file contains class::InstallerUpdate
 * @package Runalyze\Install
 */
/**
 * Updater
 * @author Hannes Christiansen
 * @package Runalyze\Install
 */
class InstallerUpdate extends Installer {
	/**
	 * All possible files to update Runalyze
	 * @var array
	 */
	protected $PossibleUpdates = array();

	/**
	 * All errors while importing sql-file
	 * @var array
	 */
	protected $Errors = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->definePath();
		$this->loadConfig();
		$this->initPossibleUpdates();
		$this->importUpdateFile();
	}

	/**
	 * Init all possible updates
	 */
	protected function initPossibleUpdates() {
		$this->PossibleUpdates[] = array(
			'file' => '',
			'text' => '----- bitte w&auml;hlen');
		// v1.5 (von 2013/??)
		$this->PossibleUpdates[] = array(
			'file' => 'update-v1.4-to-v1.5.sql',
			'text' => 'Update zu: v1.5 - vorherige Version v1.3/v1.4 (von 2013/07-08)');
		// v1.4 (von 2013/08) - keine Änderungen in der DB
		// v1.3 (von 2013/07)
		$this->PossibleUpdates[] = array(
			'file' => 'update-v1.2-to-v1.3.sql',
			'text' => 'Update zu: v1.3 - vorherige Version v1.2 (von 2012/11)');
		$this->PossibleUpdates[] = array(
			'file' => 'update-v1.1-to-v1.2.sql',
			'text' => 'Update zu: v1.2 - vorherige Version v1.1 (von 2012/07)');
		$this->PossibleUpdates[] = array(
			'file' => 'update-v1.0-to-v1.1.sql',
			'text' => 'Update zu: v1.1 - vorherige Version v1.0 (von 2012/01)');
		$this->PossibleUpdates[] = array(
			'file' => 'update-v0.6-to-v1.0alpha.sql',
			'text' => 'Update zu: v1.0 - vorherige Version v0.6 (von 2011/08)');
		$this->PossibleUpdates[] = array(
			'file' => 'update-v0.5-to-v1.0alpha.sql',
			'text' => 'Update zu: v1.0 - vorherige Version v0.5 (von 2011/07)');
		$this->PossibleUpdates[] = array(
			'file' => 'update-v0.5-to-v0.6.sql',
			'text' => 'Update zu: v0.6 - vorherige Version v0.5 (von 2011/07)');
	}

	/**
	 * Import selected file
	 */
	protected function importUpdateFile() {
		mysql_connect($this->mysqlConfig[0], $this->mysqlConfig[1], $this->mysqlConfig[2]);
		mysql_select_db($this->mysqlConfig[3]);

		if (isset($_POST['importFile']) && strlen($_POST['importFile']) > 4)
			$this->Errors = self::importSqlFile('inc/install/'.$_POST['importFile']);
	}

	/**
	 * Display the Updater
	 */
	public function display() {
		include PATH.'tpl/tpl.InstallerUpdate.php';
	}
}