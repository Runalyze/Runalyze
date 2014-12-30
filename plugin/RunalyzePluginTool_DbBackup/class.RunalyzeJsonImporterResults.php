<?php
/**
 * This file contains class::RunalyzeJsonImporterResults
 * @package Runalyze\Plugins\Tools
 */
/**
 * Importer results
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Tools
 */
class RunalyzeJsonImporterResults {
	/**
	 * Results
	 * @var array
	 */
	protected $Results = array();

	/**
	 * Add updates
	 * @param string $TableName
	 * @param int $counter
	 */
	public function addUpdates($TableName, $counter) {
		$this->add($TableName, $counter, 'update');
	}

	/**
	 * Add deletes
	 * @param string $TableName
	 * @param int $counter
	 */
	public function addDeletes($TableName, $counter) {
		$this->add($TableName, $counter, 'delete');
	}

	/**
	 * Add inserts
	 * @param string $TableName
	 * @param int $counter
	 */
	public function addInserts($TableName, $counter) {
		$this->add($TableName, $counter, 'insert');
	}

	/**
	 * Increase internal counter
	 * @param string $TableName
	 * @param int $counter
	 * @param string $key
	 */
	protected function add($TableName, $counter, $key) {
		if (!isset($this->Results[$TableName])) {
			$this->Results[$TableName] = array(
				'update' => 0,
				'delete' => 0,
				'insert' => 0
			);
		}

		$this->Results[$TableName][$key] += $counter;
	}

	/**
	 * Get internal counter
	 * @param string $TableName
	 * @param string $key
	 * @return int
	 */
	protected function get($TableName, $key) {
		if (isset($this->Results[$TableName])) {
			return $this->Results[$TableName][$key];
		}

		return 0;
	}

	/**
	 * Are there results?
	 * @param string $TableName
	 * @return bool
	 */
	public function hasResultsFor($TableName) {
		if (isset($this->Results[$TableName])) {
			return (
				$this->Results[$TableName]['delete'] > 0 ||
				$this->Results[$TableName]['update'] > 0 ||
				$this->Results[$TableName]['insert'] > 0
			);
		}
	}

	/**
	 * Get complete string
	 * @return string
	 */
	public function completeString() {
		$Rows = array();

		foreach ($this->tables() as $TableName => $String) {
			if ($this->hasResultsFor($TableName)) {
				$Rows[] = $this->stringFor($TableName, $String);
			}
		}

		if (empty($Rows)) {
			return 'Nothing changed.';
		}

		return implode('<br>', $Rows);
	}

	/**
	 * Get result string
	 * @param string $TableName
	 * @param string $String
	 * @return string
	 */
	protected function stringFor($TableName, $String) {
		$Format = '<strong>%s</strong>: %d deleted, %d updated, %d inserted';

		return sprintf($Format, $String, $this->Results[$TableName]['delete'], $this->Results[$TableName]['update'], $this->Results[$TableName]['insert']);
	}

	/**
	 * Tables with string
	 * @return array
	 */
	protected function tables() {
		return array(
			PREFIX.'clothes'		=> __('Clothes'),
			PREFIX.'conf'			=> __('Configuration'),
			PREFIX.'dataset'		=> __('dataset'),
			PREFIX.'plugin'			=> __('Plugin'),
			PREFIX.'plugin_conf'	=> __('Plugin configuration'),
			PREFIX.'shoe'			=> __('Shoes'),
			PREFIX.'sport'			=> __('Sport types'),
			PREFIX.'type'			=> __('Activity types'),
			PREFIX.'user'			=> __('Body data'),
			PREFIX.'training'		=> __('Activities'),
			PREFIX.'trackdata'		=> __('Trackdata'),
			PREFIX.'route'			=> __('Routes')
		);
	}
}