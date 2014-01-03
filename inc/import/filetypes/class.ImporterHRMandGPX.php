<?php
/**
 * This file contains class::ImporterHRMandGPX
 * @package Runalyze\Import
 */
/**
 * Combined importer for HRM/GPX
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import
 */
class ImporterHRMandGPX {
	/**
	 * Training
	 * @var TrainingObject
	 */
	protected $TrainingObject = null;

	/**
	 * GPX importer
	 * @var ImporterFiletypeGPX
	 */
	protected $GPXImporter = null;

	/**
	 * Constructor
	 * @param ImporterFiletypeHRM $HRMImporter
	 * @param ImporterFiletypeGPX $GPXImporter
	 */
	public function __construct(ImporterFiletypeHRM &$HRMImporter, ImporterFiletypeGPX &$GPXImporter) {
		$this->TrainingObject = $HRMImporter->object();
		$this->GPXImporter    = $GPXImporter;

		$this->addGPXtoObject();
	}

	/**
	 * Get object
	 * @return TrainingObject
	 */
	final public function object() {
		return $this->TrainingObject;
	}

	/**
	 * Add GPX to object
	 */
	protected function addGPXtoObject() {
		if (!$this->GPXImporter->failed())
			foreach ($this->GPXImporter->object()->getArray() as $key => $value)
				if ($this->TrainingObject->get($key) == '' || $this->TrainingObject->get($key) == 0)
					$this->TrainingObject->set($key, $value);

		if (!$this->TrainingObject->Splits()->areEmpty())
			$this->fillSplitsFromGPX();
	}

	/**
	 * Fill splits from gpx
	 */
	protected function fillSplitsFromGPX() {
		if ($this->GPXImporter->object()->hasArrayTime() && $this->GPXImporter->object()->hasArrayDistance())
			$this->TrainingObject->Splits()->fillDistancesFromArray( $this->GPXImporter->object()->getArrayTime(), $this->GPXImporter->object()->getArrayDistance() );
	}
}