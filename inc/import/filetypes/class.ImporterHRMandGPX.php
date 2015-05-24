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
	 * @var array
	 */
	static private $KEYS_TO_CHECK = array(
		'arr_time',
		'arr_lat',
		'arr_lon',
		'arr_alt',
		'arr_dist',
		'arr_heart',
		'arr_pace',
		'arr_cadence',
		'arr_power',
		'arr_temperature'
	);

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
	 * @var int
	 */
	protected $ArraySize = 0;

	/**
	 * Constructor
	 * @param ImporterFiletypeHRM $HRMImporter
	 * @param ImporterFiletypeGPX $GPXImporter
	 */
	public function __construct(ImporterFiletypeHRM &$HRMImporter, ImporterFiletypeGPX &$GPXImporter) {
		$this->TrainingObject = $HRMImporter->object();
		$this->GPXImporter    = $GPXImporter;

		$this->readArraySize();
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
	 * Read array size from hrm
	 */
	protected function readArraySize() {
		$this->ArraySize = $this->TrainingObject->hasArrayTime() ? count($this->TrainingObject->getArrayTime()) : 0;
	}

	/**
	 * Add GPX to object
	 */
	protected function addGPXtoObject() {
		if ($this->GPXImporter->failed()) {
			return;
		}

		foreach ($this->GPXImporter->object()->getArray() as $key => $value) {
			if ($this->TrainingObject->get($key) == '' || $this->TrainingObject->get($key) == 0) {
				$this->setFromGPX($key, $value);
			}
		}

		if (!$this->TrainingObject->Splits()->areEmpty())
			$this->fillSplitsFromGPX();
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	protected function setFromGPX($key, $value) {
		if ($value == '' || $value == 0) {
			return;
		}

		if (in_array($key, self::$KEYS_TO_CHECK) && $this->ArraySize > 0) {
			$array = explode(DataObject::$ARR_SEP, $value);
			$length = count($array);

			if ($length > $this->ArraySize) {
				$value = implode(DataObject::$ARR_SEP, array_slice($array, 0, $this->ArraySize));
			} elseif ($length < $this->ArraySize) {
				for ($i = 0; $i < $this->ArraySize - $length; $i++) {
					$array[] = end($array);
				}

				$value = implode(DataObject::$ARR_SEP, $array);
			}
		}

		$this->TrainingObject->set($key, $value);
	}

	/**
	 * Fill splits from gpx
	 */
	protected function fillSplitsFromGPX() {
		if ($this->GPXImporter->object()->hasArrayTime() && $this->GPXImporter->object()->hasArrayDistance())
			$this->TrainingObject->Splits()->fillDistancesFromArray( $this->GPXImporter->object()->getArrayTime(), $this->GPXImporter->object()->getArrayDistance() );
	}
}