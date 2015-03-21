<?php
/**
 * This file contains class::ParserHRMSingle
 * @package Runalyze\Import\Parser
 */

use Runalyze\Activity\Duration;

/**
 * Parser for HRM files from Polar
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserHRMSingle extends ParserAbstractSingle {
	/**
	 * Current line
	 * @var string
	 */
	protected $Line = '';

	/**
	 * Current header
	 * @var string
	 */
	protected $Header = '';

	/**
	 * Total splits time
	 * @var int
	 */
	protected $totalSplitsTime = 0;

	/**
	 *
	 * @var type 
	 */
	protected $recordsAltitude = true;

	/**
	 * Flag: uses US units (miles, mp/h, ft)
	 * @var boolean
	 */
	protected $unitsInUS = false;

	/**
	 * Factor to transform km/h or mph to s/km
	 * @var float
	 */
	protected $paceFactor = 3600;

	/**
	 * Factor to transform m or ft to m
	 * @var float
	 */
	protected $distanceFactor = 1;

	/**
	 * @var int [s]
	 */
	protected $recordingInterval = 1;

	/**
	 * @var int
	 */
	protected $totalTime = 0;

	/**
	 * Parse
	 */
	public function parse() {
		$separator = "\r\n";
		$this->Line = strtok($this->FileContent, $separator);

		while ($this->Line !== false) {
			if ($this->Line[0] == '[')
				$this->Header = substr($this->Line, 1, -1);
			else
				$this->parseLine();

			$this->Line = strtok( $separator );
		}

		$this->addDistancesToLaps();
		$this->setGPSarrays();
	}

	/**
	 * Parse line
	 */
	protected function parseLine() {
		switch ($this->Header) {
			case 'Params':
				$this->readParam();
				break;
			case 'IntTimes':
				$this->readLap();
				break;
			case 'HRData':
				$this->readHRdata();
				break;
		}
	}

	/**
	 * Read param
	 */
	private function readParam() {
		if (substr($this->Line, 0, 4) == 'Date') {
			$date = DateTime::createFromFormat('Ymd H:i', substr($this->Line, 5).' 00:00');
			$this->TrainingObject->setTimestamp( $date->getTimestamp() );
		} elseif (substr($this->Line, 0, 9) == 'StartTime') {
			$Time = new Duration(substr($this->Line, 10));
			$this->TrainingObject->setTimestamp( $this->TrainingObject->getTimestamp() + $Time->seconds() );
		} elseif (substr($this->Line, 0, 6) == 'Length') {
			$Time = new Duration(substr($this->Line, 7));
			$this->TrainingObject->setTimeInSeconds( $Time->seconds() );
		} elseif (substr($this->Line, 0, 8) == 'Interval') {
			$this->recordingInterval = (int)trim(substr($this->Line, 9));
		} elseif (substr($this->Line, 0, 4) == 'Mode') {
			$this->recordsAltitude = (substr($this->Line, 5, 1) == '1');
			$this->unitsInUS = (substr($this->Line, 7, 1) == '1');

			if ($this->unitsInUS) {
				$this->setUSfactors();
			}
		} elseif (substr($this->Line, 0, 5) == 'SMode') {
			$this->recordsAltitude = (substr($this->Line, 8, 1) == '1');
			$this->unitsInUS = (substr($this->Line, 13, 1) == '1');

			if ($this->unitsInUS) {
				$this->setUSfactors();
			}
		}
	}

	private function setUSfactors() {
		$this->paceFactor = 3600 / 1.609344;
		$this->distanceFactor = 0.305;
	}

	/**
	 * Read lap
	 */
	private function readLap() {
		if (strpos($this->Line, ':')) {
			$Time = new Duration(substr($this->Line, 0, 10));
			$this->TrainingObject->Splits()->addSplit(0, $Time->seconds() - $this->totalSplitsTime);
			$this->totalSplitsTime = $Time->seconds();
		}
	}

	/**
	 * Read heartrate
	 */
	private function readHRdata() {
		$values = preg_split('/[\s]+/', $this->Line);

		$this->totalTime += $this->recordingInterval;
		$this->gps['time_in_s'][] = $this->totalTime;
		$this->gps['heartrate'][] = (int)trim($values[0]);
		$this->gps['pace'][]      = $pace = isset($values[1]) && (int)trim($values[1]) > 0 ? round($this->paceFactor / ((int)trim($values[1]) / 10)) : 0;

		$dist = $pace > 0 ? round($this->recordingInterval/$pace, ParserAbstract::DISTANCE_PRECISION) : 0;
		$this->gps['km'][] = empty($this->gps['km']) ? $dist : $dist + end($this->gps['km']);

		if (count($values) > 3) {
			$this->gps['rpm'][]       = isset($values[2]) ? (int)trim($values[2]) : 0;
			$this->gps['altitude'][]  = isset($values[3]) ? round((int)trim($values[3]) * $this->distanceFactor) : 0;
			$this->gps['power'][]     = isset($values[4]) ? (int)trim($values[4]) : 0;
		} elseif ($this->recordsAltitude) {
			$this->gps['altitude'][]  = isset($values[2]) ? round((int)trim($values[2]) * $this->distanceFactor) : 0;
		} else {
			$this->gps['rpm'][]       = isset($values[2]) ? (int)trim($values[2]) : 0;
		}
	}

	private function addDistancesToLaps() {
		if (!empty($this->gps['time_in_s']) && !empty($this->gps['km']) && !$this->TrainingObject->Splits()->areEmpty()) {
			$this->TrainingObject->Splits()->fillDistancesFromArray($this->gps['time_in_s'], $this->gps['km']);
		}
	}
}