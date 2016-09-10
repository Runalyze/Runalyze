<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Backup;

class JsonImporterResults
{
	/** @var array */
	protected $Results = array();

	/**
	 * Add updates
	 * @param string $tableName
	 * @param int $counter
	 */
	public function addUpdates($tableName, $counter)
    {
		$this->add($tableName, $counter, 'update');
	}

	/**
	 * Add deletes
	 * @param string $tableName
	 * @param int $counter
	 */
	public function addDeletes($tableName, $counter)
    {
		$this->add($tableName, $counter, 'delete');
	}

	/**
	 * Add inserts
	 * @param string $tableName
	 * @param int $counter
	 */
	public function addInserts($tableName, $counter)
    {
		$this->add($tableName, $counter, 'insert');
	}

	/**
	 * Increase internal counter
	 * @param string $tableName
	 * @param int $counter
	 * @param string $key
	 */
	protected function add($tableName, $counter, $key)
    {
		if (!isset($this->Results[$tableName])) {
			$this->Results[$tableName] = array(
				'update' => 0,
				'delete' => 0,
				'insert' => 0
			);
		}

		$this->Results[$tableName][$key] += $counter;
	}

	/**
	 * Get internal counter
	 * @param string $tableName
	 * @param string $key
	 * @return int
	 */
	protected function get($tableName, $key)
    {
		if (isset($this->Results[$tableName])) {
			return $this->Results[$tableName][$key];
		}

		return 0;
	}

	/**
	 * Are there results?
	 * @param string $tableName
	 * @return bool
	 */
	public function hasResultsFor($tableName)
    {
		if (isset($this->Results[$tableName])) {
			return (
				$this->Results[$tableName]['delete'] > 0 ||
				$this->Results[$tableName]['update'] > 0 ||
				$this->Results[$tableName]['insert'] > 0
			);
		}

        return false;
	}

	/**
	 * Get complete string
	 * @return string
	 */
	public function completeString()
    {
		$Rows = array();

		foreach ($this->tables() as $tableName => $String) {
			if ($this->hasResultsFor($tableName)) {
				$Rows[] = $this->stringFor($tableName, $String);
			}
		}

		if (empty($Rows)) {
			return 'Nothing changed.';
		}

		return implode('<br>', $Rows);
	}

	/**
	 * Get result string
	 * @param string $tableName
	 * @param string $string
	 * @return string
	 */
	protected function stringFor($tableName, $string)
    {
		$format = '<strong>%s</strong>: %d deleted, %d updated, %d inserted';

		return sprintf($format, $string, $this->Results[$tableName]['delete'], $this->Results[$tableName]['update'], $this->Results[$tableName]['insert']);
	}

	/**
	 * Tables with string
	 * @return array
	 */
	protected function tables()
    {
		return array(
			'runalyze_conf'			=> __('Configuration'),
			'runalyze_dataset'		=> __('Dataset'),
			'runalyze_plugin'		=> __('Plugin'),
			'runalyze_plugin_conf'	=> __('Plugin configuration'),
			'runalyze_sport'		=> __('Sport types'),
			'runalyze_type'			=> __('Activity types'),
			'runalyze_user'			=> __('Body data'),
			'runalyze_training'		=> __('Activities'),
			'runalyze_trackdata'	=> __('Trackdata'),
			'runalyze_swimdata'     => __('Swimdata'),
			'runalyze_route'		=> __('Routes'),
			'runalyze_hrv'			=> __('HRV data'),
			'runalyze_equipment'			=> __('Equipment'),
			'runalyze_equipment_type'		=> __('Equipment types'),
			'runalyze_equipment_sport'		=> __('Relation: equipment types to sports'),
			'runalyze_activity_equipment'	=> __('Relation: equipment to activities'),
			'runalyze_tag'			=> __('Tags'),
			'runalyze_activtiy_tag'		=> __('Relation: tag to activities'),
			'runalyze_raceresult'	=> __('Race results')
		);
	}
}
