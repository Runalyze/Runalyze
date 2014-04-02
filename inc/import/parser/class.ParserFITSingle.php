<?php
/**
 * This file contains class::ParserFITSingle
 * @package Runalyze\Import\Parser
 */
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
	 * @var string
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
	 * Is paused?
	 * @var boolean
	 */
	protected $isPaused = true;

	/**
	 * Timestamp of last stop
	 * @var int
	 */
	protected $lastStopTimestamp = false;

	/**
	 * Parse
	 */
	public function parse() {
		// Uses another interface to not hold the complete file
	}

	/**
	 * Finish parsing
	 */
	public function finishParsing() {
		$this->setGPSarrays();
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

		if (count($values) == 3)
			$this->Values[$values[0]] = array($values[1], $values[2]);
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
					break;

				case 'event':
					$this->readEvent();
					break;

				case 'record':
					$this->readRecord();
					break;

				case 'lap':
					$this->readLap();
					break;

				case 'session':
					$this->readSession();
					break;

				case 'activity':
					break;
			}
		}
	}

	/**
	 * Read file ID
	 */
	protected function readFileId() {
		if (isset($this->Values['type']) && $this->Values['type'][1] != 'activity')
			$this->addError('FIT file is not specified as activity.');

		if (isset($this->Values['garmin_product']))
			$this->TrainingObject->setCreator($this->Values['garmin_product'][1]);

		if (isset($this->Values['time_created']))
			$this->TrainingObject->setTimestamp( strtotime((string)$this->Values['time_created'][1]) );

		$this->TrainingObject->setSportid( CONF_MAINSPORT );
	}

	/**
	 * Read session
	 */
	protected function readSession() {
		if (isset($this->Values['total_timer_time']))
			$this->TrainingObject->setTimeInSeconds( round($this->Values['total_timer_time'][0] / 1e3) );

		if (isset($this->Values['total_elapsed_time']))
			$this->TrainingObject->setElapsedTime( round($this->Values['total_elapsed_time'][0] / 1e3) );

		if (isset($this->Values['total_distance']))
			$this->TrainingObject->setDistance( round($this->Values['total_distance'][0] / 1e5, 3) );
	}

	/**
	 * Read event
	 */
	protected function readEvent() {
		if (!isset($this->Values['event_type']))
			return;

		$thisTimestamp = strtotime((string)$this->Values['timestamp'][1]);

		if ($this->Values['event_type'][1] == 'stop_all') {
			$this->isPaused = true;
			$this->lastStopTimestamp = $thisTimestamp;
		} elseif ($this->Values['event_type'][1] == 'start') {
			$this->isPaused = false;

			if ($this->lastStopTimestamp === false)
				$this->TrainingObject->setTimestamp( $thisTimestamp );
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

		if (!isset($this->Values['timestamp']))
			return;

		$this->gps['latitude'][]  = isset($this->Values['position_lat']) ? substr($this->Values['position_lat'][1], 0, -3) : 0;
		$this->gps['longitude'][] = isset($this->Values['position_long']) ? substr($this->Values['position_long'][1], 0, -3) : 0;

		$this->gps['altitude'][]  = isset($this->Values['altitude']) ? substr($this->Values['altitude'][1], 0, -3) : 0;

		$this->gps['km'][]        = isset($this->Values['distance']) ? round($this->Values['distance'][0] / 1e5, 3) : 0;
		$this->gps['heartrate'][] = isset($this->Values['heart_rate']) ? $this->Values['heart_rate'][0] : 0;
		$this->gps['rpm'][]       = isset($this->Values['cadence']) ? $this->Values['cadence'][0] : 0;

		$this->gps['time_in_s'][] = strtotime((string)$this->Values['timestamp'][1]) - $this->TrainingObject->getTimestamp() - $this->PauseInSeconds;
		$this->gps['pace'][]      = $this->getCurrentPace();
	}

	/**
	 * Read lap
	 */
	protected function readLap() {
		if (isset($this->Values['total_timer_time']) && isset($this->Values['total_distance']))
			$this->TrainingObject->Splits()->addSplit(
				$this->Values['total_distance'][0] / 1e5,
				$this->Values['total_timer_time'][0] / 1e3
			);

		if (isset($this->Values['total_calories']))
			$this->TrainingObject->addCalories($this->Values['total_calories'][0]);
	}
}