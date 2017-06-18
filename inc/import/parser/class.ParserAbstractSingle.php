<?php
/**
 * This file contains class::ParserAbstractSingle
 * @package Runalyze\Import\Parser
 */

use Runalyze\Calculation\Distribution\TrackdataAverages;
use Runalyze\Configuration;
use Runalyze\Import\Exception\UnexpectedContentException;
use Runalyze\Model\Trackdata;
use Runalyze\Profile\Sport\Mapping\EnglishLanguageMapping;
use Runalyze\Profile\Sport\SportProfile;
use Runalyze\Util\LocalTime;
use Runalyze\Util\TimezoneLookup;

/**
 * Abstract parser for one single training
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
abstract class ParserAbstractSingle extends ParserAbstract {
	/**
	 * Limit to correct cadence values
	 * @var int
	 */
	const RPM_LIMIT_FOR_CORRECTION = 130;

	/**
	 * Training object
	 * @var \TrainingObject
	 */
	protected $TrainingObject = null;

	/**
	 * Pauses to apply
	 * @var array
	 */
	protected $pausesToApply = array();

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
			'rpm'			=> array(),
			'temp'			=> array(),
			'power'			=> array(),
			'groundcontact'	=> array(),
			'oscillation'	=> array(),
			'groundcontact_balance'	=> array(),
			'stroke'        => array(),
			'stroketype'    => array(),
			'hrv'		=> array(),
            'smo2_0'         => array(),
            'smo2_1'         => array(),
            'thb_0'          => array(),
            'thb_1'          => array(),
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
	 * @throws \InvalidArgumentException
	 */
	final public function object($index = 0) {
		if ($index > 0) {
			throw new InvalidArgumentException('ParserAbstractSingle has only one training, asked for index = '.$index);
		}

		return $this->TrainingObject;
	}

	/**
	 * Set timestamp and timezone offset
	 * @param $stringWithTimezoneInformation
	 */
	protected function setTimestampAndTimezoneOffsetFrom($stringWithTimezoneInformation) {
		try {
			$DateTime = new DateTime($stringWithTimezoneInformation);

			$this->TrainingObject->setTimestamp($DateTime->getTimestamp() + $DateTime->getOffset());
			$this->TrainingObject->setTimezoneOffset(round($DateTime->getOffset() / 60));
		} catch (Exception $e) {
			// Invalid date
		}
	}

	/**
	 * Set timestamp and timezone offset with internal strtotime
	 * @param string $string if this string ends with 'Z', its interpreted as in server timezone
	 */
	protected function setTimestampAndTimezoneOffsetWithUtcFixFrom($string) {
		if (substr($string, -1) == 'Z') {
			$localTimestamp = $this->strtotime($string);

			$this->TrainingObject->setTimestamp($localTimestamp);
			$this->TrainingObject->setTimezoneOffset(round((new DateTime())->setTimestamp($localTimestamp)->getOffset() / 60));
		} else {
			$this->setTimestampAndTimezoneOffsetFrom($string);
		}
	}

	/**
	 * Interpret current timestamp of training object as server time
	 */
	protected function interpretTimestampAsServerTime() {
		$this->TrainingObject->setTimezoneOffset(round((new DateTime())->setTimestamp($this->TrainingObject->getTimestamp())->getOffset() / 60));
		$this->TrainingObject->setTimestamp(LocalTime::fromServerTime($this->TrainingObject->getTimestamp())->getTimestamp());
	}

	/**
	 * Adjusted strtotime
	 * Timestamps are given in UTC but local timezone offset has to be considered!
	 * @param $string
	 * @return int
	 */
	protected function strtotime($string) {
		if (substr($string, -1) == 'Z') {
			return LocalTime::fromServerTime(strtotime(substr($string, 0, -1).' UTC'))->getTimestamp();
		}

		return LocalTime::fromString($string)->getTimestamp();
	}

    /**
     * @param int $sportEnum
     * @param \Runalyze\Profile\Mapping\ToInternalMappingInterface|null $mapping
     * @return bool
     */
    protected function setSportTypeFromEnumIfAvailable($sportEnum, \Runalyze\Profile\Mapping\ToInternalMappingInterface $mapping = null) {
        if (null !== $mapping) {
            $sportEnum = $mapping->toInternal($sportEnum);
        }

        foreach (\Runalyze\Context::Factory()->allSports() as $sport) {
            if ($sport->getInternalProfileEnum() == $sportEnum) {
                $this->TrainingObject->setSportid($sport->id());

                return true;
            }
        }

        return false;
    }

    /**
     * @param string $sportName
     * @param \Runalyze\Profile\Mapping\ToInternalMappingInterface $mapping
     * @return bool
     */
    protected function guessSportTypeFromStringIfAvailable($sportName, \Runalyze\Profile\Mapping\ToInternalMappingInterface $mapping) {
	    $sportEnum = $mapping->toInternal($sportName);

        if (SportProfile::GENERIC !== $sportEnum) {
	        foreach (\Runalyze\Context::Factory()->allSports() as $sport) {
	            if ($sport->getInternalProfileEnum() == $sportEnum) {
	                $this->TrainingObject->setSportid($sport->id());

	                return true;
	            }
	        }

            return true;
        }

        return false;
    }

	/**
	 * Try to set sportid from creator or string
	 * @param string $String
	 * @param string $Creator optional
	 */
	protected function guessSportID($String, $Creator = '') {
        if ($this->guessSportTypeFromStringIfAvailable($String, new EnglishLanguageMapping())) {
            return;
        }

        $this->TrainingObject->setSportid(Configuration::General()->mainSport());

        $name = array($String);
		// TODO: internationalization?
		switch (mb_strtolower($Creator)) {
			case 'garmin swim':
				$name[] = 'Schwimmen';
				break;
			default:
				switch (mb_strtolower($String)) {
					case 'run':
					case 'running':
						$name[] = 'Laufen';
                        $name[] = 'Corriendo';
                        $name[] = 'Running';
                        $name[] = 'In esecuzione';
                        $name[] = 'Bieganie';
                        $name[] = 'Carrera a pie';
                        $name[] = 'Hardlopen';
                        $name[] = 'Бег';
                        $name[] = 'Fonctionnement';
                        $name[] = 'Løb';
                        $name[] = 'Corsa';
                        $name[] = 'Juoksu';
                        $name[] = 'Correndo';
                        $name[] = 'córrer';
                        $name[] = 'Spring';
                        $name[] = 'Futás';
                        $name[] = 'Koşu';
						break;
                    case 'mountain bike':
                        $name[] = 'Mountainbike';
                        $name[] = 'MTB';
                        break;
					case 'cycle':
					case 'bike':
					case 'biking':
					case 'cycling':
					case 'ergometer':
                        $name[] = 'Radfahren';
                        $name[] = 'Rennrad';
                        $name[] = 'Ciclismo';
                        $name[] = 'Biking';
                        $name[] = 'Cycling';
                        $name[] = 'Ciclisme';
                        $name[] = 'Mountainbike';
                        $name[] = 'Pyöräily';
                        $name[] = 'Велосипед';
                        $name[] = 'Fietsen';
                        $name[] = 'Bici';
                        $name[] = 'Cyclisme';
                        $name[] = 'Bicicletta';
                        $name[] = 'kolarstwo';
                        $name[] = 'Cykling';
                        $name[] = 'Vélo';
                        $name[] = 'Jalgrattasõit';
                        $name[] = 'Kerékpározás';
                        $name[] = 'Bisiklet';
						break;
					case 'swim':
					case 'swimming':
						$name[] = 'Schwimmen';
                        $name[] = 'Swimming';
                        $name[] = 'Zwemmen';
                        $name[] = 'Pływanie';
                        $name[] = 'Natación';
                        $name[] = 'Nuoto';
                        $name[] = 'Nadando';
                        $name[] = 'плавание';
                        $name[] = 'svømning';
                        $name[] = 'La natation';
                        $name[] = 'Uima';
                        $name[] = 'Natação';
                        $name[] = 'Simning';
                        $name[] = 'ujumine';
                        $name[] = 'Úszás';
                        $name[] = 'Yüzme';
                    break;
					case 'other':
						$name[] = 'Sonstiges';
                        $name[] = 'Overige';
                        $name[] = 'Otros';
                        $name[] = 'Other';
                        $name[] = 'Inny';
                        $name[] = 'Altro';
                        $name[] = 'Andre';
                        $name[] = 'Altri';
                        $name[] = 'другие';
                        $name[] = 'Pozostałe';
                        $name[] = 'anderen';
                        $name[] = 'Autres';
                        $name[] = 'altres';
                        $name[] = 'Muut';
                        $name[] = 'Outras';
                        $name[] = 'andra';
                        $name[] = 'teised';
                        $name[] = 'Egyéb';
                        $name[] = 'Diğerleri';
						break;
				}
				break;
		}

        $name = array_map('strtolower', $name);
        foreach ( \Runalyze\Context::Factory()->allSports() as $sport ) {
            if ( in_array( strtolower( $sport->name() ), $name) ) {
                $this->TrainingObject->setSportid($sport->id());
                return;
            }
        }
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
		$this->checkForBadPauses();

		$this->TrainingObject->setArrayTime( $this->gps['time_in_s'] );
		$this->TrainingObject->setArrayDistance( $this->gps['km'] );
		$this->TrainingObject->setArrayLatitude( $this->gps['latitude'] );
		$this->TrainingObject->setArrayLongitude( $this->gps['longitude'] );
		$this->TrainingObject->setArrayAltitude( $this->gps['altitude'] );
		$this->TrainingObject->setArrayHeartrate( $this->gps['heartrate'] );
		$this->TrainingObject->setArrayCadence( $this->gps['rpm'] );
		$this->TrainingObject->setArrayPower( $this->gps['power'] );
		$this->TrainingObject->setArrayTemperature( $this->gps['temp'] );
		$this->TrainingObject->setArrayGroundContact( $this->gps['groundcontact'] );
		$this->TrainingObject->setArrayVerticalOscillation( $this->gps['oscillation'] );
		$this->TrainingObject->setArrayGroundContactBalance( $this->gps['groundcontact_balance'] );
		$this->TrainingObject->setArrayStroke( $this->gps['stroke'] );
		$this->TrainingObject->setArrayStrokeType( $this->gps['stroketype'] );
		$this->TrainingObject->setArrayHRV( $this->gps['hrv'] );

        $this->TrainingObject->setArraySmo2_0( $this->gps['smo2_0'] );
        $this->TrainingObject->setArraySmo2_1( $this->gps['smo2_1'] );
        $this->TrainingObject->setArrayThb_0( $this->gps['thb_0'] );
        $this->TrainingObject->setArrayThb_1( $this->gps['thb_1'] );



        $this->setValuesFromArraysIfEmpty();
		$this->guessTimezoneFromStartPosition();
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

		$prevTime = 0;

		foreach ($this->gps['time_in_s'] as $i => $time) {
			if ($time < $prevTime) {
				throw new UnexpectedContentException('Negative time step at index '.$i.'.');
			}

			$prevTime = $time;
		}
	}

	private function checkForBadPauses() {
		$pauses = $this->TrainingObject->Pauses();
		$num = $pauses->num();

		for ($i = 0; $i < $num; ++$i) {
			if ($pauses->at($i)->duration() < 0) {
				throw new UnexpectedContentException('Negative pause detected at '.$i.'.');
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

		if ($this->TrainingObject->getPulseMax() == 0)
			$this->setMaxHeartrateFromArray();

		$this->setAveragesFromArray();
		$this->setDistanceFromGPSdata();
		$this->setActivityID();
	}

	/**
	 * Set activityID if empty
	 * Floor must be used because we don't save seconds for activities (historical)
	 */
	 private function setActivityID()
	 {
	 	if (!$this->TrainingObject->hasActivityId()) {
	 		$this->TrainingObject->setActivityId((int)floor($this->TrainingObject->getTimestamp()/60)*60);
	 	}
	 }

	/**
	 * Set average and maximum heartrate from array
	 */
	private function setMaxHeartrateFromArray() {
		$array = $this->TrainingObject->getArrayHeartrate();

		if (!empty($array)) {
			$this->TrainingObject->setPulseMax( max($array) );
		}
	}

	/**
	 * Calculate average values from trackdata arrays
	 *
	 * Remember: vertical ratio, stride length and estimated power are calculated in Activity\Model\Inserter
	 */
	private function setAveragesFromArray() {
		$Trackdata = new Trackdata\Entity([
			Trackdata\Entity::TIME => $this->TrainingObject->getArrayTime(),
			Trackdata\Entity::HEARTRATE => $this->TrainingObject->getArrayHeartrate(),
			Trackdata\Entity::CADENCE => $this->TrainingObject->getArrayCadence(),
			Trackdata\Entity::VERTICAL_OSCILLATION => $this->TrainingObject->getArrayVerticalOscillation(),
			Trackdata\Entity::GROUNDCONTACT => $this->TrainingObject->getArrayGroundContact(),
			Trackdata\Entity::GROUNDCONTACT_BALANCE => $this->TrainingObject->getArrayGroundContactBalance(),
			Trackdata\Entity::POWER => $this->TrainingObject->getArrayPower()
		]);

		if ($Trackdata->isEmpty()) {
			return;
		}

		if (!$Trackdata->has(Trackdata\Entity::TIME)) {
			$Trackdata->set(Trackdata\Entity::TIME, range(1, $Trackdata->num()));
		}

		$this->setAveragesFrom(new TrackdataAverages($Trackdata, [
			Trackdata\Entity::HEARTRATE,
			Trackdata\Entity::CADENCE,
			Trackdata\Entity::VERTICAL_OSCILLATION,
			Trackdata\Entity::GROUNDCONTACT,
			Trackdata\Entity::GROUNDCONTACT_BALANCE,
			Trackdata\Entity::POWER
		]));
	}

	/**
	 * @param \Runalyze\Calculation\Distribution\TrackdataAverages $averages
	 */
	private function setAveragesFrom(TrackdataAverages $averages) {
		if ($this->TrainingObject->getPulseAvg() == 0 && $averages->average(Trackdata\Entity::HEARTRATE) > 0) {
			$this->TrainingObject->setPulseAvg(round($averages->average(Trackdata\Entity::HEARTRATE)));
		}

		if ($this->TrainingObject->getCadence() == 0 && $averages->average(Trackdata\Entity::CADENCE) > 0) {
			$this->TrainingObject->setCadence(round($averages->average(Trackdata\Entity::CADENCE)));
		}

		if ($averages->average(Trackdata\Entity::VERTICAL_OSCILLATION) > 0) {
			$this->TrainingObject->setVerticalOscillation(round($averages->average(Trackdata\Entity::VERTICAL_OSCILLATION)));
		}

		if ($averages->average(Trackdata\Entity::GROUNDCONTACT) > 0) {
			$this->TrainingObject->setGroundContactTime(round($averages->average(Trackdata\Entity::GROUNDCONTACT)));
		}

		if ($averages->average(Trackdata\Entity::GROUNDCONTACT_BALANCE) > 0) {
			$this->TrainingObject->setGroundContactBalance(round($averages->average(Trackdata\Entity::GROUNDCONTACT_BALANCE)));
		}

		if ($this->TrainingObject->getPower() == 0 && $averages->average(Trackdata\Entity::POWER) > 0) {
			$this->TrainingObject->setPower(round($averages->average(Trackdata\Entity::POWER)));
		}
	}

	/**
	 * Calculate distance
	 */
	private function setDistanceFromGPSdata() {
		if (!empty($this->gps['latitude']) && !empty($this->gps['longitude']) && empty($this->gps['km'])) {
			$num = count($this->gps['latitude']);
			$this->gps['km'] = array(0);
			$lastDistance = 0;
			$lastValidIndex = 0;

			for ($i = 1; $i < $num; ++$i) {
			    if ($this->gps['latitude'][$i] != 0.0 || $this->gps['longitude'][$i] != 0.0) {
                    $step = round(
                        Runalyze\Model\Route\Entity::gpsDistance(
                            $this->gps['latitude'][$lastValidIndex],
                            $this->gps['longitude'][$lastValidIndex],
                            $this->gps['latitude'][$i],
                            $this->gps['longitude'][$i]
                        ),
                        ParserAbstract::DISTANCE_PRECISION
                    );
                    $lastValidIndex = $i;
                } else {
			        $step = 0.0;
                }

				$this->gps['km'][] = $lastDistance + $step;
				$lastDistance += $step;
			}

			$this->TrainingObject->setArrayDistance( $this->gps['km'] );
			$this->TrainingObject->setDistance( end($this->gps['km']) );
		}
	}

	/**
	 * Use time zone from start position to correct time zone from file
	 */
	private function guessTimezoneFromStartPosition() {
		$latitudes = array_filter($this->gps['latitude']);
		$longitudes = array_filter($this->gps['longitude']);

		if (!empty($latitudes) && !empty($longitudes)) {
			$Lookup = new TimezoneLookup();

			if ($Lookup->isPossible()) {
				$timezone = $Lookup->getTimezoneForCoordinate(reset($longitudes), reset($latitudes));

				if (null !== $timezone && $timezone != '') {
					$this->adjustTimestampAndOffsetForNewTimezone($timezone);
				}
			}
		}
	}

	/**
	 * @param string $timezone
	 */
	private function adjustTimestampAndOffsetForNewTimezone($timezone) {
		$newOffset = (new \DateTime(null, new \DateTimeZone($timezone)))->setTimestamp($this->TrainingObject->getTimestamp())->getOffset() / 60;

		if (null === $this->TrainingObject->getTimezoneOffset()) {
			// Don't correct the timestamp if the currently used offset is unknown
			// (This may happen for files with something link 'Time = 16:00' as info)
		} else {
			$this->TrainingObject->setTimestamp($this->TrainingObject->getTimestamp() + 60*($newOffset - $this->TrainingObject->getTimezoneOffset()));
		}

		$this->TrainingObject->setTimezoneOffset($newOffset);
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
	 * Correct cadence if needed
	 *
	 * Cadence values are clearly defined by http://www8.garmin.com/xmlschemas/TrackPointExtensionv1.xsd
	 * as "... measured in revolutions per minute." but it seems that
	 * Strava exports them in spm (steps per minute).
	 *
	 * @see https://github.com/Runalyze/Runalyze/issues/1367
	 */
	protected function correctCadenceIfNeeded() {
		if (!empty($this->gps['rpm'])) {
			$avg = array_sum($this->gps['rpm']) / count($this->gps['rpm']);

			if ($avg > self::RPM_LIMIT_FOR_CORRECTION) {
				$this->gps['rpm'] = array_map(function ($v) {
					return round($v/2);
				}, $this->gps['rpm']);
			}
		}
	}

	/**
	 * Apply pauses
	 */
	protected function applyPauses() {
		if (!empty($this->pausesToApply)) {
			$num = count($this->gps['time_in_s']);
			$hasHR = !empty($this->gps['heartrate']);
			$keys = array_keys($this->gps);
			$pauseInSeconds = 0;
			$pauseIndex = 0;
			$pauseTime = $this->pausesToApply[$pauseIndex]['time'];
			$pauseUntil = 0;
			$isPause = false;
			$hrStart = 0;

			for ($i = 0; $i < $num; $i++) {
				if (!$isPause && $this->gps['time_in_s'][$i] > $pauseTime) {
					if ($pauseIndex < count($this->pausesToApply)) {
						$isPause = true;
						$hrStart = !$hasHR ? 0 : (isset($this->gps['heartrate'][$i-1]) ? $this->gps['heartrate'][$i-1] : $this->gps['heartrate'][$i]);
						$pauseInSeconds += $this->pausesToApply[$pauseIndex]['duration'];
						$pauseTime = $this->pausesToApply[$pauseIndex]['time'];
						$pauseUntil = $this->pausesToApply[$pauseIndex]['duration'] + $pauseTime;
						$pauseIndex++;
						$pauseTime = ($pauseIndex < count($this->pausesToApply)) ? $this->pausesToApply[$pauseIndex]['time'] : PHP_INT_MAX;
					}
				}

				if ($isPause && $this->gps['time_in_s'][$i] >= $pauseUntil) {
					$isPause = false;

					$this->TrainingObject->Pauses()->add(
						new \Runalyze\Model\Trackdata\Pause(
							$this->pausesToApply[$pauseIndex-1]['time'],
							$this->pausesToApply[$pauseIndex-1]['duration'],
							$hrStart,
							$hasHR ? $this->gps['heartrate'][$i] : 0
						)
					);
				}

				if ($isPause) {
					foreach ($keys as $key) {
						if (isset($this->gps[$key][$i])) {
							unset($this->gps[$key][$i]);
						}
					}
				} else {
					$this->gps['time_in_s'][$i] -= $pauseInSeconds;
				}
			}
		}
	}

	/**
	 * Search in database for a string and get the ID
	 * @param string $table
	 * @param string $string
	 * @return int
	 */
	private static function getIDforDatabaseString($table, $string) {
            $Result = Cache::get($table.$string);
            if(is_null($Result)) {
                if ($table == 'type')
                    $SearchQuery = 'SELECT id FROM '.PREFIX.$table.' WHERE name LIKE "%'.$string.'%" OR abbr="'.$string.'" LIMIT 1';
                else
                    $SearchQuery = 'SELECT id FROM '.PREFIX.$table.' WHERE name LIKE "%'.$string.'%" LIMIT 1';

                        $Result = DB::getInstance()->query($SearchQuery)->fetch();
                        Cache::set($table.$string,$Result,'60');
            }

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
