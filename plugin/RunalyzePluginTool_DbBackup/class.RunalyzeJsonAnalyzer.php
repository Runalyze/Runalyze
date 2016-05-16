<?php
/**
 * This file contains class::RunalyzeJsonAnalyzer
 * @package Runalyze\Plugins\Tools
 */

use Runalyze\Util\File\GZipReader;

/**
 * RunalyzeJsonAnalyzer
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Tools
 */
class RunalyzeJsonAnalyzer {
	/**
	 * @var string
	 */
	protected $VersionString = '';

	/**
	 * Number of rows per table
	 * @var array
	 */
	protected $NumberOf = array();

	/**
	 * Construct
	 * @param string $fileName
	 */
	public function __construct($fileName) {
		$Reader = new GZipReader(FRONTEND_PATH.$fileName);

		while (!$Reader->eof()) {
			$Line = trim($Reader->readLine());

			if (substr($Line, 0, 8) == '{"TABLE"') {
				$TableName = substr($Line, 10, -2);
				$this->NumberOf[$TableName] = 0;
			} elseif ($Line != '' && $Line{0} == '{') {
				$this->NumberOf[$TableName]++;
			} elseif (substr($Line, 0, 8) == 'version=') {
				$this->VersionString = substr($Line, 8);
			}
		}

		$Reader->close();
	}

	/**
	 * Count rows for table
	 * @param string $tableName
	 * @return int
	 */
	public function count($tableName) {
		if (isset($this->NumberOf[$tableName])) {
			return $this->NumberOf[$tableName];
		}

		return 0;
	}

	/**
	 * File is okay
	 * @return bool
	 */
	public function fileIsOkay() {
		return $this->versionIsOkay() && $this->expectedTablesAreThere();
	}

	/**
	 * @return bool
	 */
	public function versionIsOkay() {
		return (self::extractMajorAndMinorVersion(RUNALYZE_VERSION) == self::extractMajorAndMinorVersion($this->VersionString));
	}

	/**
	 * @string
	 */
	public function fileVersion() {
		if ($this->VersionString == '') {
			return 'unknown';
		}	

		return 'v'.$this->VersionString;
	}

	/**
	 * All expected tables are there
	 * @return bool
	 */
	protected function expectedTablesAreThere() {
		$Expected = $this->expectedTables();
		$Found = array_keys($this->NumberOf);

		return (sort($Expected) == sort($Found));
	}

	/**
	 * Errors
	 * @return array
	 */
	public function errors() {
		$Expected = $this->expectedTables();
		$Found = array_keys($this->NumberOf);
		$Errors = array();

		foreach (array_diff($Expected, $Found) as $MissingKey) {
			$Errors[] = 'Missing table: '.$MissingKey;
		}

		foreach (array_diff($Found, $Expected) as $AdditionalKey) {
			$Errors[] = 'Additional table: '.$AdditionalKey;
		}

		return $Errors;
	}

	/**
	 * Expected tables
	 * @return array
	 */
	protected function expectedTables() {
		return array(
			'runalyze_account',
			'runalyze_conf',
			'runalyze_dataset',
			'runalyze_hrv',
			'runalyze_plugin',
			'runalyze_plugin_conf',
			'runalyze_route',
			'runalyze_sport',
			'runalyze_training',
			'runalyze_trackdata',
			'runalyze_type',
			'runalyze_user',
			'runalyze_equipment_type',
			'runalyze_equipment_sport',
			'runalyze_equipment',
			'runalyze_activity_equipment',
			'runalyze_activity_tag',
			'runalyze_tag',
			'runalyze_raceresult'
		);
	}

	/**
	 * Extract major and minor version, i.e. 'X.Y' of any 'X.Y[.Z][-abc]'
	 * @param string $versionString
	 * @return string
	 */
	public static function extractMajorAndMinorVersion($versionString) {
		if (preg_match('/^(\d+\.\d+)/', $versionString, $matches)) {
			return $matches[1];
		}

		return '';
	}
}