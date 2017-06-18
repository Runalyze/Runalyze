<?php
/**
 * This file contains class::ParserFITSingle
 * @package Runalyze\Import\Parser
 */

use Runalyze\Configuration;
use Runalyze\Import\Exception\ParserException;

/**
 * Parser for FIT files from ANT
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserFITSingle extends ParserAbstractSingle {
	/**
	 * Current line
	 * @var string
	 */
	protected $Line = '';

	/**
	 * Current header
	 * @var array
	 */
	protected $Header = array();

	/**
	 * Current values
	 * @var array
	 */
	protected $Values = array();

	/**
	 * Total pause time
	 * @var int
	 */
	protected $PauseInSeconds = 0;

	/**
	 * Is this a swimming activity?
	 * @var bool
	 */
	protected $isSwimming = false;

	/**
	 * Is paused?
	 * @var boolean
	 */
	protected $isPaused = false;

	/**
	 * Was paused?
	 * @var bool
	 */
	protected $wasPaused = false;

	/**
	 * Timestamp of last stop
	 * @var int
	 */
	protected $lastStopTimestamp = false;

	/** @var float [s] */
	protected $compressedTotalTime = 0;

	/** @var float [16m] */
	protected $compressedTotalDistance16 = 0;

	/** @var float [16m] */
	protected $compressedLastDistance16 = 0;

	/** @var string */
	protected $softwareVersion = '';

	/** @var array */
	protected $DeveloperFieldMappingForRecord = array(
		'0_Ground_Time' => ['stance_time', 10],
        'saturated_hemoglobin_percent' => ['smo2_0', 0.1],
        'total_hemoglobin_conc' => ['thb_0', 1]
	);

	/** @var array */
	protected $nativeFieldMappingForRecord = array(
		// TODO: native fields aller verwendeten Arrays
		3 => ['heart_rate'],
		4 => ['cadence', ['default' => 1, 'SPM' => 0.5]],
		5 => ['distance', ['default' => 1, 'm' => 100]],
		7 => ['power'],
		39 => ['vertical_oscillation', ['default' => 1, 'Centimeters' => 100]],
		54 => [['thb_0', 'thb_1'], 100],
		57 => [['smo2_0', 'smo2_1'], 1]
	);

	/** @var array */
	protected $DeveloperFieldMappingForSession = array();

	/** @var array */
	protected $nativeFieldMappingForSession = array(
		9 => ['total_distance', ['default' => 1, 'm' => 100]],
		11 => ['total_calories'],
		44 => ['pool_length', ['default' => 1, 'm' => 100]],
	);

	/** @var array */
	protected $DeveloperFieldMappingForLap = array();

	/** @var array */
	protected $nativeFieldMappingForLap = array(
		9 => ['total_distance', ['default' => 1, 'm' => 100]],
	);

	protected function readFieldDescription() {
		switch ($this->Values['native_mesg_num'][1]) {
			case 'record':
				$this->readFieldDescriptionFor($this->nativeFieldMappingForRecord, $this->DeveloperFieldMappingForRecord);
				break;
			case 'session':
				$this->readFieldDescriptionFor($this->nativeFieldMappingForSession, $this->DeveloperFieldMappingForSession);
				break;
			case 'lap':
				$this->readFieldDescriptionFor($this->nativeFieldMappingForLap, $this->DeveloperFieldMappingForLap);
				break;
		}
	}

	protected function readFieldDescriptionFor(array &$nativeFieldMapping, array &$fieldMapping) {
		if (
			isset($this->Values['native_field_num']) &&
			isset($nativeFieldMapping[$this->Values['native_field_num'][0]]) &&
			!empty($nativeFieldMapping[$this->Values['native_field_num'][0]][0]) &&
			isset($this->Values['developer_data_index']) &&
			isset($this->Values['field_name'])
		) {
			$fieldname = $this->Values['developer_data_index'][0].'_'.str_replace(['"', ' '], ['', '_'], $this->Values['field_name'][0]);
			$fieldname = preg_replace_callback('/(\W)/i', function(array $char) {
			    return sprintf('_%02x_', ord($char[0]));
			}, preg_replace('/(\s+)/i', '_', $fieldname));

			$nativeFieldNum = $this->Values['native_field_num'][0];
			$unitDefinition = str_replace('"', '', $this->Values['units'][0]);

			$mappingKey = $nativeFieldMapping[$nativeFieldNum][0];
			$mappingFactor = isset($nativeFieldMapping[$nativeFieldNum][1]) ? $nativeFieldMapping[$nativeFieldNum][1] : 1;

			if (is_array($mappingFactor)) {
				$mappingFactor = isset($mappingFactor[$unitDefinition]) ? $mappingFactor[$unitDefinition] : $mappingFactor['default'];
			}

			if (is_array($mappingKey)) {
				$mappingKey = array_shift($nativeFieldMapping[$nativeFieldNum][0]);
			}

			$fieldMapping[$fieldname] = [$mappingKey, $mappingFactor];
		}
	}

	/**
	 * Parse
	 */
	public function parse() {
		// Uses another interface to not hold the complete file
	}

	/**
	 * Start a new activity at current point
	 */
	public function startNewActivity() {
		$creator = $this->TrainingObject->getCreator();
		$creatorDetails = $this->TrainingObject->getCreatorDetails();
		$offset = $this->TrainingObject->getTimezoneOffset();

		$this->TrainingObject = new TrainingObject(DataObject::$DEFAULT_ID);
		$this->TrainingObject->setTimestamp(PHP_INT_MAX);
		$this->TrainingObject->setTimezoneOffset($offset);
		$this->TrainingObject->setCreator($creator);
		$this->TrainingObject->setCreatorDetails($creatorDetails);

		$this->isPaused = false;
		$this->wasPaused = false;
		$this->isSwimming = false;
		$this->PauseInSeconds = 0;
		$this->lastStopTimestamp = false;

		foreach (array_keys($this->gps) as $key) {
			$this->gps[$key] = array();
		}
	}

	/**
	 * Finish parsing
	 */
	public function finishParsing() {
		$this->applyPauses();
		$this->setGPSarrays();
		$this->fixForSuunto();

		// TODO: lookup timezone and correct timestamp if startpoint is not in user's timezone
	}

	/**
	 * Parse line
	 */
	public function parseLine($line) {
		if ($line[0] == '=') {
			if (empty($this->Header)) {
				$this->setHeaderFrom($line);
			} else {
				$this->interpretCurrentValues();
				$this->Header = array();
				$this->Values = array();
			}
		} elseif ($line[0] == '-') {
			$this->addValueFrom($line);
		} elseif ($line[0] == '#') {
			// Unimportant
		}
	}

	/**
	 * Set header
	 * @param string $line
	 */
	protected function setHeaderFrom($line) {
		$MessageInfo = explode(' ', substr($line, 2));

		foreach ($MessageInfo as $Info) {
			$Info = explode('=', $Info);
			$this->Header[$Info[0]] = $Info[1];
		}
	}

	/**
	 * Add value
	 * @param string $line
	 */
	protected function addValueFrom($line) {
		$line = substr($line, 4);
		$values = explode('=', $line);

		if (count($values) == 3) {
			$this->Values[$values[0]] = array($values[1], $values[2]);
		} elseif (count($values) == 2) {
			$this->Values[$values[0]] = array($values[1]);
		}
	}

	/**
	 * Interpret values
	 */
	protected function interpretCurrentValues() {
		if (isset($this->Header['NAME'])) {
			switch ($this->Header['NAME']) {
				case 'file_id':
					$this->readFileId();
					break;

				case 'file_creator':
					break;
				case 'device_info':
					$this->readDeviceInfo();
					break;

				case 'sport':
					$this->readSport();
					break;

				case 'event':
					$this->readEvent();
					break;

				case 'record':
					$this->readRecord();
					break;

				case 'hrv':
					$this->readHRV();
					break;

				case 'lap':
					$this->readLap();
					break;

				case 'session':
					$this->readSession();
					break;

				case 'length':
					$this->readLength();
					break;

				case 'field_description':
					$this->readFieldDescription();
					break;

				case 'activity':
					break;

				case 'user_profile':
					$this->readUserProfile();
					break;
			}
		} elseif (isset($this->Header['NUMBER'])) {
			switch ($this->Header['NUMBER']) {
				case 79:
					$this->readUndocumentedUserData();
					break;
                case 140:
                    $this->readUndocumentedDataBlob140();
                    break;
			}
		}
	}

	/**
	 * Read file ID
	 * @throws \Runalyze\Import\Exception\ParserException
	 */
	protected function readFileId() {
		if (isset($this->Values['type']) && $this->Values['type'][1] != 'activity') {
			throw new ParserException('FIT file is not specified as activity.');
		}

		if (isset($this->Values['time_created']))
			$this->setTimestampAndTimezoneOffsetWithUtcFixFrom((string)$this->Values['time_created'][1]);

		$this->TrainingObject->setSportid( Configuration::General()->mainSport() );

		if (isset($this->Values['manufacturer']))
			$this->TrainingObject->setCreator($this->Values['manufacturer'][1]);
	}

	/**
	 * Read device info
	 */
	protected function readDeviceInfo() {
		if (isset($this->Values['device_index']) && $this->Values['device_index'][0] == 0) {
			if (isset($this->Values['garmin_product'])) {
				$this->TrainingObject->setCreator($this->Values['garmin_product'][1]);
			}

			if (isset($this->Values['software_version'])) {
				$this->softwareVersion = $this->Values['software_version'][1];
				$this->TrainingObject->setCreatorDetails('firmware '.$this->softwareVersion);
			}
		}
	}

	/**
	 * Read session
	 */
	protected function readSession() {
		$this->mapDeveloperFieldsToNativeFieldsFor($this->DeveloperFieldMappingForSession);

		if (isset($this->Values['total_timer_time']))
			$this->TrainingObject->setTimeInSeconds( round($this->Values['total_timer_time'][0] / 1e3) + $this->TrainingObject->getTimeInSeconds() );

		if (isset($this->Values['total_elapsed_time']))
			$this->TrainingObject->setElapsedTime( round($this->Values['total_elapsed_time'][0] / 1e3) + $this->TrainingObject->getElapsedTime() );

		if (isset($this->Values['total_distance']))
			$this->TrainingObject->setDistance( round($this->Values['total_distance'][0] / 1e5, 3) + $this->TrainingObject->getDistance() );

		if (isset($this->Values['total_calories']))
			$this->TrainingObject->setCalories( $this->Values['total_calories'][0] + $this->TrainingObject->getCalories() );

		if (isset($this->Values['total_strokes']))
			$this->TrainingObject->setTotalStrokes($this->Values['total_strokes'][0]);

		if (isset($this->Values['avg_swimming_cadence']))
			$this->TrainingObject->setCadence($this->Values['avg_swimming_cadence'][0]);

		if (isset($this->Values['pool_length']))
			$this->TrainingObject->setPoolLength($this->Values['pool_length'][0]);

		if (isset($this->Values['sport']) && !$this->tryToSetFitSportEnum($this->Values['sport'][0]))
			$this->guessSportID($this->Values['sport'][1]);

		if (isset($this->Values['total_training_effect']) && $this->Values['total_training_effect'][0] >= 10.0 && $this->Values['total_training_effect'][0] <= 50.0)
			$this->TrainingObject->setFitTrainingEffect($this->Values['total_training_effect'][0]/10);
	}

    /**
     * @param int|string $sportEnum
     * @return bool
     */
	protected function tryToSetFitSportEnum($sportEnum) {
	    return $this->setSportTypeFromEnumIfAvailable((int)$sportEnum, new \Runalyze\Profile\Sport\Mapping\FitSdkMapping());
    }

	/**
	 * Read sport
	 */
	protected function readSport() {
	    if (isset($this->Values['sport']) && $this->tryToSetFitSportEnum($this->Values['sport'][0])) {
	        return;
        }

		if (isset($this->Values['name'])) {
			$this->guessSportID(substr($this->Values['name'][0], 1, -1));
		}

		if ($this->TrainingObject->get('sportid') == Configuration::General()->mainSport()) {
			if (isset($this->Values['sport'])) {
				$this->guessSportID($this->Values['sport'][1]);
			}
		}
	}

	/**
	 * Read user data
	 */
	protected function readUserProfile() {
		if (isset($this->Values['xxx39'])) {
			$this->TrainingObject->setFitVO2maxEstimate(round((float)$this->Values['xxx39'][1] * 3.5, 2));
		}
	}

	/**
	 * Read undocumented user data
	 */
	protected function readUndocumentedUserData() {
		if (isset($this->Values['unknown0']) && $this->TrainingObject->getFitVO2maxEstimate() == 0) {
			$this->TrainingObject->setFitVO2maxEstimate(round((int)$this->Values['unknown0'][1] * 3.5 / 1024, 2));
		}
	}

	protected function readUndocumentedDataBlob140() {
        if (isset($this->Values['unknown17']) && $this->TrainingObject->getFitPerformanceCondition()) {
            $this->TrainingObject->setFitPerformanceConditionEnd(100 + (float)$this->Values['unknown17'][1]);
        }
    }

	/**
	 * Read event
	 */
	protected function readEvent() {
		if (isset($this->Values['event']) && isset($this->Values['data'])) {
			switch ((int)$this->Values['event'][1]) {
				case 37:
					$this->TrainingObject->setFitVO2maxEstimate((int)$this->Values['data'][1]);
					return;

				case 38:
					$this->TrainingObject->setFitRecoveryTime((int)$this->Values['data'][1]);
					return;

				case 39:
					$creator = $this->TrainingObject->getCreator();

					// TODO: this may need more device and firmware specific conditions
					if (
                        substr($creator, 0, 5) == 'fr630' ||
						substr($creator, 0, 7) == 'fr735xt' ||
						substr($creator, 0, 6) == 'fenix3' ||
                        substr($creator, 0, 6) == 'fenix5'
					) {
					    if ((int)$this->Values['data'][1] >= 0 && (int)$this->Values['data'][1] <= 255) {
                            $this->TrainingObject->setFitPerformanceCondition((int)$this->Values['data'][1]);
                        }
					} else {
						$this->TrainingObject->setFitHRVscore((int)$this->Values['data'][1]);
					}

					return;

			}
		}

		if (!isset($this->Values['event']) || $this->Values['event'][1] != 'timer' || !isset($this->Values['event_type']))
			return;

		$thisTimestamp = $this->strtotime((string)$this->Values['timestamp'][1]);

		if (!empty($this->gps['time_in_s']) && ($this->Values['event_type'][1] == 'stop_all' || $this->Values['event_type'][1] == 'stop')) {
			$this->isPaused = true;
			$this->lastStopTimestamp = $thisTimestamp;
		} elseif ($this->Values['event_type'][1] == 'start') {
			if ($this->isPaused && ($thisTimestamp - $this->TrainingObject->getTimestamp()) < end($this->gps['time_in_s'])) {
				$this->pausesToApply[] = array(
					'time' => $this->lastStopTimestamp - $this->TrainingObject->getTimestamp(),
					'duration' => ($thisTimestamp - $this->lastStopTimestamp)
				);
			} elseif ($this->isPaused) {
				$this->wasPaused = true;
			}

			$this->isPaused = false;

			if ($this->lastStopTimestamp === false)
				$this->setTimestampAndTimezoneOffsetWithUtcFixFrom((string)$this->Values['timestamp'][1]);
			elseif ($thisTimestamp > $this->lastStopTimestamp)
				$this->PauseInSeconds += $thisTimestamp - $this->lastStopTimestamp;
		}
	}

	/**
	 * Read record
	 */
	protected function readRecord() {
		if ($this->isPaused) // Should not happen?
			return;

		if ($this->isSwimming)
			return;

		$this->mapDeveloperFieldsToNativeFieldsFor($this->DeveloperFieldMappingForRecord);

		if (isset($this->Values['compressed_speed_distance'])) {
			$time = $this->parseCompressedSpeedDistance();
			$last = -1;
		} else {
			if (!isset($this->Values['timestamp']))
				return;

			if (empty($this->gps['time_in_s'])) {
				$startTime = $this->strtotime((string)$this->Values['timestamp'][1]);

				if ($startTime < $this->TrainingObject->getTimestamp()) {
					$this->setTimestampAndTimezoneOffsetWithUtcFixFrom((string)$this->Values['timestamp'][1]);
				}
			}
			$time = $this->strtotime((string)$this->Values['timestamp'][1]) - $this->TrainingObject->getTimestamp() - $this->PauseInSeconds;
			$last = end($this->gps['time_in_s']);

			if ($this->wasPaused) {
				$this->TrainingObject->Pauses()->add(
					new \Runalyze\Model\Trackdata\Pause(
						$last,
						$this->strtotime((string)$this->Values['timestamp'][1]) - $this->lastStopTimestamp,
						end($this->gps['heartrate']),
						isset($this->Values['heart_rate']) ? (int)$this->Values['heart_rate'][0] : 0
					)
				);

				$this->wasPaused = false;
			}
		}

		if ($time < $last) {
			return;
		}

		$this->gps['latitude'][]  = isset($this->Values['position_lat']) ? substr($this->Values['position_lat'][1], 0, -4) : 0;
		$this->gps['longitude'][] = isset($this->Values['position_long']) ? substr($this->Values['position_long'][1], 0, -4) : 0;

		$this->gps['altitude'][]  = isset($this->Values['altitude']) ? substr($this->Values['altitude'][1], 0, -4) : 0;

		$this->gps['km'][]        = isset($this->Values['distance']) ? round($this->Values['distance'][0] / 1e5, ParserAbstract::DISTANCE_PRECISION) : end($this->gps['km']);
		$this->gps['heartrate'][] = isset($this->Values['heart_rate']) ? (int)$this->Values['heart_rate'][0] : 0;
		$this->gps['rpm'][]       = isset($this->Values['cadence']) ? (int)$this->Values['cadence'][0] : 0;
		$this->gps['power'][]     = isset($this->Values['power']) ? (int)$this->Values['power'][0] : 0;
		//$this->gps['left_right'][]     = isset($this->Values['left_right_balance']) ? (int)$this->Values['left_right_balance'][0] : 0;

		$this->gps['temp'][]      = isset($this->Values['temperature']) ? (int)$this->Values['temperature'][0] : 0;

		$this->gps['time_in_s'][] = $time;

		//Running Dynamics
		$this->gps['groundcontact'][] = isset($this->Values['stance_time']) ? round($this->Values['stance_time'][0]/10) : 0;
		$this->gps['oscillation'][]   = isset($this->Values['vertical_oscillation']) ? round($this->Values['vertical_oscillation'][0]/10) : 0;
		$this->gps['groundcontact_balance'][] = isset($this->Values['stance_time_balance']) ? (int)$this->Values['stance_time_balance'][0] : 0;

        // Fit developer fields
        $this->gps['smo2_0'][] = isset($this->Values['smo2_0']) ? (int)$this->Values['smo2_0'][0] : 0;
        $this->gps['smo2_1'][] = isset($this->Values['smo2_1']) ? (int)$this->Values['smo2_1'][0] : 0;
        $this->gps['thb_0'][] = isset($this->Values['thb_0']) ? (int)$this->Values['thb_0'][0] : 0;
        $this->gps['thb_1'][] = isset($this->Values['thb_1']) ? (int)$this->Values['thb_1'][0] : 0;

        if ($time === $last) {
			$this->mergeRecord();
		}
	}

	protected function mapDeveloperFieldsToNativeFieldsFor(array $developerFieldMapping) {
		foreach ($developerFieldMapping as $devFieldName => $nativeData) {
			$nativeFieldName = $nativeData[0];
			$nativeFactor = $nativeData[1];

			if (isset($this->Values[$devFieldName]) && ($this->Values[$devFieldName][0] != 0 || !isset($this->Values[$nativeFieldName]))) {
				$this->Values[$devFieldName][0] *= $nativeFactor;
				$this->Values[$nativeFieldName] = $this->Values[$devFieldName];
			}
		}
	}

	/**
	 * Merge current record
	 */
	protected function mergeRecord() {
		end($this->gps['time_in_s']);
		$i = key($this->gps['time_in_s']);

		foreach (array_keys($this->gps) as $key) {
			if (isset($this->gps[$key][$i])) {
				$last = $this->gps[$key][$i - 1];
				$current = array_pop($this->gps[$key]);

				if ($current != 0 && $last == 0) {
					$this->gps[$key][$i - 1] = $current;
				}
			}
		}
	}

	/**
	 * @see FIT SDK, e.g. at https://github.com/dgaff/fitsdk/blob/7f38d911388b7cdc3db7bf0318239352928faa8b/c/examples/decode/decode.c#L132-L146
	 * @return int current time
	 */
	protected function parseCompressedSpeedDistance() {
		$values = explode(',', $this->Values['compressed_speed_distance'][1]);

		if (count($values) == 3) {
			$speed100 = $values[0] | (($values[1] & 0x0F) << 8);

			$distance16 = ($values[1] >> 4) | ($values[2] << 4);
			$distance16diff = ($distance16 - $this->compressedLastDistance16) & 0x0FFF;
			$this->compressedTotalDistance16 += $distance16diff;
			$this->compressedLastDistance16 = $distance16;

			$this->compressedTotalTime += ($distance16diff/16.0) / ($speed100/100.0);
			$this->Values['distance'][0] = 100 * $this->compressedTotalDistance16/16.0;
		}

		return round($this->compressedTotalTime);
	}

	/**
	 * Read lap
	 */
	protected function readLap() {
		$this->mapDeveloperFieldsToNativeFieldsFor($this->DeveloperFieldMappingForLap);

		if (isset($this->Values['total_timer_time']) && isset($this->Values['total_distance']) && round($this->Values['total_timer_time'][0] / 1e3) > 0)
			$this->TrainingObject->Splits()->addSplit(
				$this->Values['total_distance'][0] / 1e5,
				$this->Values['total_timer_time'][0] / 1e3
			);
	}

	/**
	 * Read length
	 */
	protected function readLength() {
		if (!$this->isSwimming) {
			foreach (array_keys($this->gps) as $key) {
				$this->gps[$key] = array();
			}

			$this->isSwimming = true;
		}

		$this->gps['stroke'][] = isset($this->Values['total_strokes']) ? (int)$this->Values['total_strokes'][0] : 0;
		$this->gps['stroketype'][] = isset($this->Values['swim_stroke']) ? (int)$this->Values['swim_stroke'][0] : 0;
		$this->gps['rpm'][] = isset($this->Values['avg_swimming_cadence']) ? (int)$this->Values['avg_swimming_cadence'][0] : 0;

		if (empty($this->gps['time_in_s'])) {
			$this->setTimestampAndTimezoneOffsetWithUtcFixFrom((string)$this->Values['start_time'][1]);
			$this->gps['time_in_s'][] = round(((int)$this->Values['total_timer_time'][0])/1000);
		} else {
			$this->gps['time_in_s'][] = end($this->gps['time_in_s']) + round(((int)$this->Values['total_timer_time'][0])/1000);
		}
	}

	/**
	 * Read hrv
	 */
	protected function readHRV() {
		if (!$this->isPaused) {
			$values = explode(',', $this->Values['time'][1]);

			foreach ($values as $value) {
				if ($value != '65535') {
					$this->gps['hrv'][] = 1000*(double)substr($value, 0, -2);
				}
			}
		}
	}

	/**
	 * @see https://github.com/Runalyze/Runalyze/issues/1886
	 */
	protected function fixForSuunto() {
		if ('suunto' == $this->TrainingObject->getCreator()) {
			$this->TrainingObject->setTimeInSeconds($this->TrainingObject->getArrayTimeLastPoint());
			$this->finishLaps();
		}
	}

	/**
	 * Finish laps
	 */
	protected function finishLaps() {
		$totalTime = $this->TrainingObject->getTimeInSeconds() > 0 ? $this->TrainingObject->getTimeInSeconds() : end($this->gps['time_in_s']);

		$this->TrainingObject->Splits()->addLastSplitToComplete(end($this->gps['km']), $totalTime);
	}
}
