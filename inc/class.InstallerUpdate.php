<?php
/**
 * This file contains the class::InstallerUpdate for updating Runalyze
 */
/**
 * Class: InstallerUpdate
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Error
 * @uses class::Mysql
 */
class InstallerUpdate {
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
		$this->loadConfig();
		$this->initPossibleUpdates();
		$this->importUpdateFile();
	}

	/**
	 * Init all possible updates
	 */
	protected function initPossibleUpdates() {
		$this->PossibleUpdates[] = array('file' => '', 'text' => '----- bitte w&auml;hlen');
		$this->PossibleUpdates[] = array('file' => 'update-v0.6-to-v1.0alpha.sql', 'text' => 'v1.0 alpha - Update von v0.6 (Mitte August)');
		$this->PossibleUpdates[] = array('file' => 'update-v0.5-to-v1.0alpha.sql', 'text' => 'v1.0 alpha - Update von v0.5 (Mitte Juli)');
		$this->PossibleUpdates[] = array('file' => 'update-v0.5-to-v0.6.sql', 'text' => 'v0.6 - Update von v0.5 (Mitte Juli)');
	}

	/**
	 * Import selected file
	 */
	protected function importUpdateFile() {
		if (isset($_POST['importFile']) && strlen($_POST['importFile']) > 4)
			$this->Errors = self::importSqlFile($_POST['importFile']);
	}

	/**
	 * Display the Updater
	 */
	public function display() {
		include 'tpl/tpl.InstallerUpdate.php';
	}
}
?>