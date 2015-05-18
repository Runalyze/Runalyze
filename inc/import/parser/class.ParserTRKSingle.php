<?php
/**
 * This file contains class::ParserTRKSingle
 * @package Runalyze\Import\Parser
 */

/**
 * Parser for trk files from TwoNav / O-Synce
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserTRKSingle extends ParserAbstractSingle {
	/**
	 * @var boolean
	 */
	protected $isStarted = false;

	/**
	 * @var boolean
	 */
	protected $isPaused = false;

	/**
	 * @var int
	 */
	protected $starttime = 0;

	/**
	 * @var int
	 */
	protected $pauseInSeconds = 0;

	/**
	 * @var float
	 */
	protected $latitude = false;

	/**
	 * @var float
	 */
	protected $longitude = false;

	/**
	 * Parse
	 */
	public function parse() {
		$separator = "\r\n";
		$line = strtok($this->FileContent, $separator);

		while ($line !== false) {
			$this->parseLine($line);
			$line = strtok($separator);
		}

		$this->finishData();
		$this->setGPSarrays();
	}

	/**
	 * Finish
	 */
	protected function finishData() {
		$this->TrainingObject->setTimestamp($this->starttime);
	}

	/**
	 * Parse line
	 * @param string $line
	 */
	protected function parseLine($line) {
		$firstChar = substr($line, 0, 1);
		switch ($firstChar) {
			case 'T':
				$this->readTrackpoint($line);
				break;
		}
	}

	/**
	 * Read trackpoint
	 * @param string $line
	 */
	private function readTrackpoint($line) {
		$values = preg_split('/[\s]+/', $line);
		$num = count($values);

		if ($num < 7)
			return;

		$latitude = floatval($values[2]);
		$longitude = floatval($values[3]);
		$time = strtotime($values[4].' '.$values[5]) - $this->starttime - $this->pauseInSeconds;

		if (!$this->isStarted) {
			$this->isStarted = true;
			$this->starttime = $time;
			$time = 0;
		}

		if ($values[6] == 'N' && $time > 0) {
			$this->isPaused = true;

			if ($time == end($this->gps['time_in_s'])) {
				return;
			}
		} elseif ($this->isPaused) {
			$this->isPaused = false;

			$pause = $time > 0 ? $time - end($this->gps['time_in_s']) : 0;
			$time -= $pause;
			$this->pauseInSeconds += $pause;

			$this->latitude = $latitude;
			$this->longitude = $longitude;

			return;
		}

		$this->gps['time_in_s'][] = $time;
		$this->gps['km'][] = $this->latitude === false ? 0
				: end($this->gps['km']) + round(GpsData::distance($latitude, $longitude, $this->latitude, $this->longitude), ParserAbstract::DISTANCE_PRECISION);
		$this->gps['latitude'][] = $latitude;
		$this->gps['longitude'][] = $longitude;
		$this->gps['altitude'][] = ($num > 7 && $values[7] != '-1') ? round($values[7]) : 0;
		$this->gps['temp'][] = ($num > 14 && $values[14] != '-1') ? round($values[14]) : 0;
		$this->gps['heartrate'][] = ($num > 17 && $values[17] != '-1') ? round($values[17]) : 0;

		$this->latitude = $latitude;
		$this->longitude = $longitude;
	}
}