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
	 * @var array
	 */
	protected $FurtherInstructions = array();

	/** @var bool */
	protected $CacheWasCleared = false;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->definePath();
		$this->loadConfig();
		$this->initAutoloader();
		$this->initLanguage();
		$this->loadConsts();
		$this->tryToClearCache();

		$this->initPossibleUpdates();
		$this->importUpdateFile();
	}

	/**
	 * Set up Autloader 
	 */
	protected function initAutoloader() {
		require_once FRONTEND_PATH.'/system/class.Autoloader.php';
		new Autoloader();
	}

	/**
	 * Try to clear cache
	 */
	protected function tryToClearCache() {
		try {
			new Cache();
			Cache::clean();

			$this->CacheWasCleared = true;
		} catch (Exception $e) {
			$this->CacheWasCleared = false;
		}
	}

	/**
	 * Init all possible updates
	 */
	protected function initPossibleUpdates() {
		$this->PossibleUpdates[] = array(
			'file'	=> 'update-v2.3-to-v2.4.sql',
			'from'	=> 'v2.3',
			'to'	=> 'v2.4',
			'date'	=> '2015/12',
			'instruction'	=> [
				sprintf(
					__('We have changed some paths. Please set write permissions for all directories in %s.'),
					'<em>data/</em>'
				),
				sprintf(
					__('If you are using local srtm files, please move them from %s to %s.'),
					'<em>inc/data/gps/srtm</em>', '<em>data/srtm</em>'
				),
				sprintf(
					__('Please add %s (or whatever port your database connection requires) to your %s file.'),
					'<em>$port = 3306;</em>', '<em>data/config.php</em>'
				),
				$this->instructionToRunScript('build/global.routefix.php'),
				$this->instructionToRunScript('refactor-night.php')
			]
		);
		$this->PossibleUpdates[] = array(
			'file'	=> 'update-v2.2-to-v2.3.sql',
			'from'	=> 'v2.2',
			'to'	=> 'v2.3',
			'date'	=> '2015/10',
			'instruction'	=> $this->instructionToRunScript('refactor-geohash.php')
		);
		$this->PossibleUpdates[] = array(
			'file'	=> 'update-v2.1-to-v2.2.sql',
			'from'	=> 'v2.1',
			'to'	=> 'v2.2',
			'date'	=> '2015/07',
			'instruction'	=> $this->instructionToRunScript('refactor-equipment.php')
		);
		$this->PossibleUpdates[] = array(
			'file'	=> 'update-v2.0-to-v2.1.sql',
			'from'	=> 'v2.0',
			'to'	=> 'v2.1',
			'date'	=> '2015/02'
		);
		// v2.0 (von 2015/02)
		$this->PossibleUpdates[] = array(
			'file'	=> 'update-v1.5-to-v2.0.sql',
			'from'	=> 'v1.5',
			'to'	=> 'v2.0',
			'date'	=> '2014/01',
			'instruction'	=> $this->instructionToRunScript('refactor-db.php')
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
	 * Get message to run specific script
	 * @param string $script relative to /runalyze/
	 * @return string
	 */
	protected function instructionToRunScript($script) {
		return sprintf(
			__('You are required to run the script %s. Please set your database connection within that file first and then run it via cli or in your browser.'),
			'<a href="'.$script.'">'.$script.'</a>'
		);
	}

	/**
	 * @return boolean
	 */
	protected function hasErrors() {
		return (!empty($this->Errors) && (count($this->Errors) > 1 || strlen(trim($this->Errors[0])) > 3));
	}

	/**
	 * @return boolean
	 */
	protected function triesToUpdate() {
		return isset($_POST['importFile']) && isset($this->PossibleUpdates[$_POST['importFile']]);
	}

	/**
	 * Import selected file
	 */
	protected function importUpdateFile() {
		$this->connectToDatabase($this->mysqlConfig[3], $this->mysqlConfig[0], $this->mysqlConfig[4], $this->mysqlConfig[1], $this->mysqlConfig[2]);

		if ($this->triesToUpdate()) {
			$update = $this->PossibleUpdates[$_POST['importFile']];
			$this->Errors = $this->importSqlFile('inc/install/'.$update['file']);

			if (isset($update['instruction'])) {
				if (is_array($update['instruction'])) {
					$this->FurtherInstructions = array_merge($this->FurtherInstructions, $update['instruction']);
				} else {
					$this->FurtherInstructions[] = $update['instruction'];
				}
			}
		}
	}

	/**
	 * @return bool
	 */
	protected function installationHasAccounts() {
		return (1 == $this->PDO->query('SELECT 1 FROM `'.PREFIX.'account` LIMIT 1')->fetchColumn());
	}

	/**
	 * Display the Updater
	 */
	public function display() {
		include PATH.'tpl/tpl.InstallerUpdate.php';
	}
}
