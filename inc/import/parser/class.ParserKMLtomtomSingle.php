<?php
/**
 * This file contains class::ParserKMLtomtomSingle
 * @package Runalyze\Import\Parser
 */

use Runalyze\Configuration;

/**
 * Parser for KML files from Tom Tom
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserKMLtomtomSingle extends ParserAbstractSingleXML {
	/**
	 * Pause in seconds
	 * @var int
	 */
	protected $PauseInSeconds = 0;

	/**
	 * Pause index
	 * @var int
	 */
	protected $PauseIndex = 0;

	/**
	 * Pause times
	 * @var array
	 */
	protected $PauseTimes = array();

	/**
	 * Pause times
	 * @var array
	 */
	protected $ResumeTimes = array();

	/**
	 * Parse
	 */
	protected function parseXML() {
		if ($this->isCorrectXML()) {
			$this->preparePauses();
			$this->parseGeneralValues();
			$this->parseSteps();
			$this->parseExtendedData();
			$this->setGPSarrays();
		} else {
			$this->throwNoXMLError();
		}
	}

	/**
	 * Is a correct file given?
	 * @return bool
	 */
	protected function isCorrectXML() {
		$tracks = $this->XML->xpath('//gx:Track');

		return !empty($tracks);
	}

	/**
	 * Add error: incorrect file
	 */
	protected function throwNoXMLError() {
		$this->addError( __('Given XML object does not contain any track. &lt;gx:Track&gt;-tag could not be located.') );
	}

	/**
	 * Parse general values
	 */
	protected function parseGeneralValues() {
		$when = $this->XML->xpath('//when');

		$this->TrainingObject->setTimestamp( strtotime((string)$when[0]) );
		$this->TrainingObject->setSportid( Configuration::General()->runningSport() );
	}

	/**
	 * Parse steps
	 */
	protected function parseSteps() {
		$this->parseCoordinates();
		$this->parseTimesteps();
	}

	/**
	 * Parse coordinates
	 */
	protected function parseCoordinates() {
		foreach ($this->XML->xpath('//gx:coord') as $coord) {
			$parts = explode(' ', (string)$coord);

			if (count($parts) == 3) {
				$this->gps['latitude'][]  = $parts[1];
				$this->gps['longitude'][] = $parts[0];
				$this->gps['altitude'][]  = $parts[2];
			}
		}
	}

	/**
	 * Parse timesteps
	 */
	protected function parseTimesteps() {
		$DSTcorrector = date('I') - date('I', $this->TrainingObject->getTimestamp());

		foreach ($this->XML->xpath('//when') as $step) {
			$time = strtotime((string)$step);

			if ($this->hasMorePauses()) {
				$currentTime = strftime('%T', $time + $DSTcorrector * 3600);
				if ($currentTime == $this->ResumeTimes[$this->PauseIndex]) {
					$this->PauseInSeconds += $this->PauseTimes[$this->PauseIndex];
					$this->PauseIndex++;
				}
			}

			$this->gps['time_in_s'][] = $time - $this->TrainingObject->getTimestamp() - $this->PauseInSeconds;
		}

		$this->TrainingObject->setElapsedTime( $time - $this->TrainingObject->getTimestamp() );
	}

	/**
	 * Parse extended data
	 */
	protected function parseExtendedData() {
		foreach ($this->XML->xpath('//gx:SimpleArrayData') as $array) {
			switch ($array['name']) {
				case 'calories':
					$this->parseExtendedCalories($array);
					break;
				case 'distance':
					$this->parseExtendedDistance($array);
					break;
				case 'heartrate':
					$this->parseExtendedHeartrate($array);
					break;
			}
		}
	}

	/**
	 * Parse calories
	 * @param SimpleXMLElement $array
	 */
	protected function parseExtendedCalories(SimpleXMLElement $array) {
		$values = $array->xpath('gx:value');
		$kcal   = array_pop($values);

		$this->TrainingObject->setCalories( (int)$kcal );
	}

	/**
	 * Parse distance
	 * @param SimpleXMLElement $array
	 */
	protected function parseExtendedDistance(SimpleXMLElement $array) {
		$values = $array->xpath('gx:value');

		foreach ($values as $value)
			$this->gps['km'][] = round((float)$value/1000, ParserAbstract::DISTANCE_PRECISION);
	}

	/**
	 * Parse heartrate
	 * @param SimpleXMLElement $array
	 */
	protected function parseExtendedHeartrate(SimpleXMLElement $array) {
		$values = $array->xpath('gx:value');

		foreach ($values as $value)
			$this->gps['heartrate'][] = (int)$value;
	}

	/**
	 * Prepare pauses
	 */
	protected function preparePauses() {
		$start = 0;

		foreach ($this->XML->Document->Folder as $folder) {
			if ($folder['id'] == 'pause_resume') {
				foreach ($folder->Placemark as $mark) {
					if ($mark->styleUrl == '#pause') {
						$start = strtotime((string)$mark->description.'Z');
					} elseif ($mark->styleUrl == '#resume') {
						$end = strtotime((string)$mark->description.'Z');

						$this->ResumeTimes[] = strftime('%T', $end);
						$this->PauseTimes[] = ($end - $start) % DAY_IN_S;
					}
				}
			}
		}
	}

	/**
	 * Has more pauses?
	 * @return boolean
	 */
	protected function hasMorePauses() {
		return $this->PauseIndex < count($this->PauseTimes);
	}
}