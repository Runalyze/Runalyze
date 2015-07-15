<?php
/**
 * This file contains class::RunalyzeJsonAnalyzer
 * @package Runalyze\Plugins\Tools
 */
/**
 * RunalyzeJsonAnalyzer
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Tools
 */
class RunalyzeJsonAnalyzer {
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
		$Reader = new BigFileReaderGZip($fileName);

		while (!$Reader->eof()) {
			$Line = trim($Reader->readLine());

			if (substr($Line, 0, 8) == '{"TABLE"') {
				$TableName = substr($Line, 10, -2);
				$this->NumberOf[$TableName] = 0;
			} elseif ($Line != '' && $Line{0} == '{') {
				$this->NumberOf[$TableName]++;
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
			'runalyze_clothes',
			'runalyze_conf',
			'runalyze_dataset',
			'runalyze_plugin',
			'runalyze_plugin_conf',
			'runalyze_route',
			'runalyze_shoe',
			'runalyze_sport',
			'runalyze_training',
			'runalyze_trackdata',
			'runalyze_type',
			'runalyze_user'
		);
	}
}