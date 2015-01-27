<?php
/**
 * This file contains class::ParserAbstractSingle
 * @package Runalyze\Import\Parser
 */

use Runalyze\Configuration;

/**
 * Abstract parser for one single training
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
abstract class ParserAbstractSingle extends ParserAbstract {
	/**
	 * Training object
	 * @var \TrainingObject
	 */
	protected $TrainingObject = null;

	/**
	 * Internal array for gps data
	 * @var array
	 */
	protected $gps = array(
			'time_in_s'     => array(),
			'latitude'      => array(),
			'longitude'     => array(),
			'altitude'      => array(),
			'km'            => array(),
			'heartrate'     => array(),
			'pace'          => array(),
			'rpm'			=> array(),
			'temp'			=> array(),
			'power'			=> array(),
			'groundcontact'	=> array(),
			'oscillation'	=> array()
		);

	/**
	 * Previous distance
	 * 
	 * For pace calculation.
	 * 
	 * @var int
	 */
	private $paceDist = 0;

	/**
	 * Previous time
	 * 
	 * For pace calculation.
	 * 
	 * @var int
	 */
	private $paceTime = 0;

	/**
	 * Constructor
	 * @param string $FileContent file content
	 */
	public function __construct($FileContent) {
		parent::__construct($FileContent);

		$this->TrainingObject = new TrainingObject( DataObject::$DEFAULT_ID );
	}

	/**
	 * Get training objects
	 * @return array array of TrainingObjects
	 */
	final public function objects() {
		return array($this->object());
	}

	/**
	 * Get training object
	 * @param int $index optional
	 * @return \TrainingObject
	 */
	final public function object($index = 0) {
		if ($index > 0)
			Error::getInstance()->addDebug('ParserAbstractSingle has only one training, asked for index = '.$index);

		return $this->TrainingObject;
	}

	/**
	 * Try to set sportid from creator or string
	 * @param string $String
	 * @param string $Creator optional
	 */
	protected function guessSportID($String, $Creator = '') {
		// TODO: internationalization?
		switch (mb_strtolower($Creator)) {
			case 'garmin swim':
				$String = 'Schwimmen';
				break;
			default:
				switch (mb_strtolower($String)) {
					case 'run':
					case 'running':
						$String = 'Laufen';
						break;
					case 'cycle':
					case 'bike':
					case 'biking':
					case 'mountain bike':
					case 'cycling':
					case 'ergometer':
						$String = 'Radfahren';
						break;
					case 'swim':
					case 'swimming':
						$String = 'Schwimmen';
						break;
					case 'other':
						$String = 'Sonstiges';
						break;
				}
				break;
		}

		$this->TrainingObject->setSportid( self::getIDforDatabaseString('sport', $String) );
	}

	/**
	 * Try to set typeid from string
	 * @param string $String
	 */
	protected function guessTypeID($String) {
		$this->TrainingObject->setTypeid( self::getIDforDatabaseString('type', $String) );
	}

	/**
	 * Set GPS data
	 */
	protected function setGPSarrays() {
		$this->removeInvalidEntriesFromGPSarrays();

		if (empty($this->gps['pace']) && !empty($this->gps['time_in_s']) && !empty($this->gps['km']))
			$this->setPaceFromDistanceAndTime();

		$this->TrainingObject->setArrayTime( $this->gps['time_in_s'] );
		$this->TrainingObject->setArrayDistance( $this->gps['km'] );
		$this->TrainingObject->setArrayLatitude( $this->gps['latitude'] );
		$this->TrainingObject->setArrayLongitude( $this->gps['longitude'] );
		$this->TrainingObject->setArrayAltitude( $this->gps['altitude'] );
		$this->TrainingObject->setArrayHeartrate( $this->gps['heartrate'] );
		$this->TrainingObject->setArrayPace( $this->gps['pace'] );
		$this->TrainingObject->setArrayCadence( $this->gps['rpm'] );
		$this->TrainingObject->setArrayPower( $this->gps['power'] );
		$this->TrainingObject->setArrayTemperature( $this->gps['temp'] );
		$this->TrainingObject->setArrayGroundContact( $this->gps['groundcontact'] );
		$this->TrainingObject->setArrayVerticalOscillation( $this->gps['oscillation'] );

		$this->setValuesFromArraysIfEmpty();
	}

	/**
	 * Clear gps arrays with only one or invalid entries
	 */
	private function removeInvalidEntriesFromGPSarrays() {
		foreach ($this->gps as $key => $values) {
			if (count($values) <= 1) {
				$this->gps[$key] = array();
			} elseif (min($values) == 0 && max($values) == 0) {
				$this->gps[$key] = array();
			}
		}
	}

	/**
	 * Set values like distance, duration, etc. from gps data if they are empty
	 */
	private function setValuesFromArraysIfEmpty() {
		if (!$this->TrainingObject->hasDistance()) {
			if ($this->TrainingObject->hasArrayDistance())
				$this->TrainingObject->setDistance( $this->TrainingObject->getArrayDistanceLastPoint() );
			elseif (!$this->TrainingObject->Splits()->areEmpty())
				$this->TrainingObject->setDistance( $this->TrainingObject->Splits()->totalDistance() );
		}

		if ($this->TrainingObject->getTimeInSeconds() == 0) {
			if ($this->TrainingObject->hasArrayTime())
				$this->TrainingObject->setTimeInSeconds( $this->TrainingObject->getArrayTimeLastPoint() );
			elseif (!$this->TrainingObject->Splits()->areEmpty())
				$this->TrainingObject->setTimeInSeconds( $this->TrainingObject->Splits()->totalTime() );
		}

		if ($this->TrainingObject->getPulseAvg() == 0 && $this->TrainingObject->getPulseMax() == 0)
			$this->setAvgAndMaxHeartrateFromArray();

		$this->setAvgCadenceFromArray();
		$this->setAvgPowerFromArray();
		$this->setTemperatureFromArray();
		$this->setRunningDynamicsFromArray();
	}

	/**
	 * Set average and maximum heartrate from array
	 */
	private function setAvgAndMaxHeartrateFromArray() {
		$array = $this->TrainingObject->getArrayHeartrate();
		if (!empty($array) && max($array) > 30) {
			$array = array_filter($array, 'ParserAbstract__ArrayFilterForLowEntries');

			$this->TrainingObject->setPulseAvg( round(array_sum($array)/count($array)) );
			$this->TrainingObject->setPulseMax( max($array) );
		}
	}

	/**
	 * Set average cadence from array
	 */
	private function setAvgCadenceFromArray() {
		$array = $this->TrainingObject->getArrayCadence();

		if (!empty($array) && max($array) > 30) {
			$array = array_filter($array, 'ParserAbstract__ArrayFilterForLowEntries');

			$this->TrainingObject->setCadence( round(array_sum($array)/count($array)) );
		}
	}

	/**
	 * Set running dynamics from array
	 */
	private function setRunningDynamicsFromArray() {
		$groundContact = $this->TrainingObject->getArrayGroundContact();
		$oscillation = $this->TrainingObject->getArrayVerticalOscillation();

		if (!empty($groundContact) && max($groundContact) > 30) {
			$groundContact = array_filter($groundContact, 'ParserAbstract__ArrayFilterForLowEntries');

			$this->TrainingObject->setGroundContactTime( round(array_sum($groundContact)/count($groundContact)) );
		}

		if (!empty($oscillation) && max($oscillation) > 30) {
			$oscillation = array_filter($oscillation);

			$this->TrainingObject->setVerticalOscillation( round(array_sum($oscillation)/count($oscillation)) );
		}
	}

	/**
	 * Set average power from array
	 */
	private function setAvgPowerFromArray() {
		$array = $this->TrainingObject->getArrayPower();

		if (!empty($array) && max($array) > 30) {
			$array = array_filter($array, 'ParserAbstract__ArrayFilterForLowEntries');

			$this->TrainingObject->setPower( round(array_sum($array)/count($array)) );
		}
	}

	/**
	 * Set average temperature from array
	 */
	private function setTemperatureFromArray() {
		$array = $this->TrainingObject->getArrayTemperature();

		if (!empty($array) && (min($array) != max($array) || min($array) != 0))
			$this->TrainingObject->setTemperature( round(array_sum($array)/count($array)) );
	}

	/**
	 * Set pace from distance and time
	 */
	private function setPaceFromDistanceAndTime() {
		$num = count($this->gps['km']);
		$prevDist = 0;
		$prevTime = 0;

		for ($i = 0; $i < $num; ++$i) {
			$currDist = $this->gps['km'][$i];
			$currTime = $this->gps['time_in_s'][$i];

			if ($currDist > $prevDist) {
				$pace = ($currTime - $prevTime) / ($currDist - $prevDist);
	
				$prevDist = $currDist;
				$prevTime = $currTime;
			} else {
				$pace = 0;
			}

			$this->gps['pace'][] = round($pace);
		}
	}

	/**
	 * Get current pace
	 * @return int
	 */
	final protected function getCurrentPace() {
		$currDist = end($this->gps['km']);
		$currTime = end($this->gps['time_in_s']);

		if ($currDist > $this->paceDist) {
			$pace = ($currTime - $this->paceTime) / ($currDist - $this->paceDist);

			$this->paceDist = $currDist;
			$this->paceTime = $currTime;

			return round($pace);
		}

		return 0;
	}

	/**
	 * Search in database for a string and get the ID
	 * @param string $table
	 * @param string $string
	 * @return int
	 */
	static private function getIDforDatabaseString($table, $string) {
            $Result = Cache::get($table.$string);
            if(is_null($Result)) {
		if ($table == 'type')
			$SearchQuery = 'SELECT id FROM '.PREFIX.$table.' WHERE name LIKE "%'.$string.'%" OR abbr="'.$string.'" LIMIT 1';
		else
			$SearchQuery = 'SELECT id FROM '.PREFIX.$table.' WHERE name LIKE "%'.$string.'%" LIMIT 1';

                $Result = DB::getInstance()->query($SearchQuery)->fetch();
                Cache::set($table.$string,$Result,'60');
            }

		if ($Result === false)
			return ($table == 'sport') ? Configuration::General()->mainSport() : 0;

		return $Result['id'];
	}
}

/**
 * Filter-function: Remove all entries lower than 30 from array
 * @param mixed $value
 * @return boolean 
 */
function ParserAbstract__ArrayFilterForLowEntries($value) {
	return ($value > 30);
}