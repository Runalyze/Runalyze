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
		$this->initLanguage();
		$this->loadConsts();

		$this->initPossibleUpdates();
		$this->importUpdateFile();
	}

	/**
	 * Init all possible updates
	 */
	protected function initPossibleUpdates() {
		$this->PossibleUpdates[] = array(
			'file'	=> 'update-v2.0-to-v2.1.sql',
			'from'	=> 'v2.0',
			'to'	=> 'v2.1',
			'date'	=> '2015/05'
		);
		// v2.0 (von 2015/02)
		$this->PossibleUpdates[] = array(
			'file'	=> 'update-v1.5-to-v2.0.sql',
			'from'	=> 'v1.5',
			'to'	=> 'v2.0',
			'date'	=> '2014/01'
		);
		$this->PossibleUpdates[] = array(
			'file'	=> 'update-v2.0alpha-to-v2.0beta.sql',
			'from'	=> 'v2.0alpha',
			'to'	=> 'v2.0',
			'date'	=> '2015/01'
		);
		// v1.5 (von 2014/01)
		$this->PossibleUpdates[] = array(
			'file'	=> 'update-v1.4-to-v1.5.sql',
			'from'	=> 'v1.3/v1.4',
			'to'	=> 'v1.5',
			'date'	=> '2013/07-08'
		);
		// v1.4 (von 2013/08) - keine Änderungen in der DB
		// v1.3 (von 2013/07)
		$this->PossibleUpdates[] = array(
			'file'	=> 'update-v1.2-to-v1.3.sql',
			'from'	=> 'v1.2',
			'to'	=> 'v1.3',
			'date'	=> '2012/11'
		);
		$this->PossibleUpdates[] = array(
			'file'	=> 'update-v1.1-to-v1.2.sql',
			'from'	=> 'v1.1',
			'to'	=> 'v1.2',
			'date'	=> '2012/07'
		);
		$this->PossibleUpdates[] = array(
			'file' => 'update-v1.0-to-v1.1.sql',
			'from'	=> 'v1.0',
			'to'	=> 'v1.1',
			'date'	=> '2012/01'
		);
		$this->PossibleUpdates[] = array(
			'file' => 'update-v0.6-to-v1.0alpha.sql',
			'from'	=> 'v0.6',
			'to'	=> 'v1.0',
			'date'	=> '2011/08'
		);
		$this->PossibleUpdates[] = array(
			'file' => 'update-v0.5-to-v1.0alpha.sql',
			'from'	=> 'v0.5',
			'to'	=> 'v1.0',
			'date'	=> '2011/07'
		);
		$this->PossibleUpdates[] = array(
			'file' => 'update-v0.5-to-v0.6.sql',
			'from'	=> 'v0.5',
			'to'	=> 'v0.6',
			'date'	=> '2011/07'
		);
	}

	/**
	 * Import selected file
	 */
	protected function importUpdateFile() {
		$this->connectToDatabase($this->mysqlConfig[3], $this->mysqlConfig[0], $this->mysqlConfig[1], $this->mysqlConfig[2]);

		if (isset($_POST['importFile']) && strlen($_POST['importFile']) > 4)
			$this->Errors = $this->importSqlFile('inc/install/'.$_POST['importFile']);
	}

	/**
	 * Display the Updater
	 */
	public function display() {
		include PATH.'tpl/tpl.InstallerUpdate.php';
	}
}