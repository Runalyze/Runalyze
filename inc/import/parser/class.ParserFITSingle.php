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
 * @author undertrained
 * @package Runalyze\Import\Parser
 */
class ParserFITSingle extends ParserAbstractSingle {
	/**
	 * Current FIT data
	 * @var adriangibbons\phpFITFileAnalysis
	 */
	protected $fitData = null;

	/**
	 * Is this a swim session?
	 * @var bool
	 */
	protected $isSwim = false;

	/**
	 * When did the session start?
	 * @var int
	 */
	protected $startTime = 0;

	/**
	 * Set FIT data
	 */
	public function setFitData(adriangibbons\phpFITFileAnalysis $fit) {
		$this->fitData = $fit;
	}

	/**
	 * Parse
	 * @throws \Runalyze\Import\Exception\ParserException
	 */
	public function parse() {
		$creatorDetails = '';

		$this->TrainingObject = new TrainingObject(DataObject::$DEFAULT_ID);
		$this->TrainingObject->setTimestamp(PHP_INT_MAX);
		$this->TrainingObject->setTimezoneOffset(null);

		if (isset($this->fitData->data_mesgs['file_id']['type']) &&
		    $this->fitData->enumData('file', $this->fitData->data_mesgs['file_id']['type']) != 'activity')
			throw new ParserException('FIT file is not specified as activity.');

		if (isset($this->fitData->data_mesgs['device_info']['manufacturer']))
			$this->TrainingObject->setCreator($this->fitData->manufacturer());

		if (isset($this->fitData->data_mesgs['file_creator']['software_version']))
			$creatorDetails = 'firmware '.$this->fitData->data_mesgs['file_creator']['software_version'];
		if (isset($this->fitData->data_mesgs['file_id']['product']))
			$creatorDetails .= ($creatorDetails == '' ? '' : ', ') . 'product '.$this->fitData->product();
		if (isset($this->fitData->data_mesgs['file_id']['serial_number']))
			$creatorDetails .= ($creatorDetails == '' ? '' : ', ') . 'serial number '.$this->fitData->data_mesgs['file_id']['serial_number'];
		$this->TrainingObject->setCreatorDetails($creatorDetails);

		if (isset($this->fitData->data_mesgs['session']['timestamp']))
			$this->startTime = $this->fitData->data_mesgs['session']['timestamp'];
		else if (isset($this->fitData->data_mesgs['file_id']['time_created']))
			$this->startTime = $this->fitData->data_mesgs['file_id']['time_created'];

		$this->guessSportID(mb_strtolower($this->fitData->sport()), $this->fitData->manufacturer() . ' ' . $this->fitData->product());

		$this->isSwim = (substr(mb_strtolower($this->fitData->sport()), 0, 4) == 'swim');

		/* try to map all internal fields to FIT fields */
		foreach (array_keys($this->gps) as $key) {
			$fitkey = $this->mapGPStoFITkey($key, $this->isSwim);
			$fittype = $this->mapFITtoType($fitkey);
			if (isset($this->fitData->data_mesgs[$fittype][$fitkey]))
				$this->gps[$key] = array_values($this->fitData->data_mesgs[$fittype][$fitkey]);
		}
		/* time_in_s from FIT is an array of timestamps, we need an array of seconds since activity_start */
		if (isset($this->gps['time_in_s'])) {
			if ($this->gps['time_in_s'][0] < $this->startTime)
				$this->startTime = $this->gps['time_in_s'][0];
			foreach($this->gps['time_in_s'] as &$val)
				$val -= $this->startTime;
		}

		$this->setTimestampAndTimezoneOffsetWithUtcFixFrom(strftime("%Y-%m-%dT%H:%M:%S%Z", $this->startTime));

		if (isset($this->fitData->data_mesgs['session']['num_laps']) &&
		    isset($this->fitData->data_mesgs['session']['first_lap_index'])) {
			for ($lap = $this->fitData->data_mesgs['session']['first_lap_index']; $lap < $this->fitData->data_mesgs['session']['num_laps']; $lap++) {
				if (isset($this->fitData->data_mesgs['lap']['total_timer_time'][$lap]) &&
				    isset($this->fitData->data_mesgs['lap']['total_distance'][$lap]) &&
				    $this->fitData->data_mesgs['lap']['total_timer_time'][$lap] > 0) {
					$this->TrainingObject->Splits()->addSplit(
					    $this->fitData->data_mesgs['lap']['total_distance'][$lap],
					    $this->fitData->data_mesgs['lap']['total_timer_time'][$lap]
					);
				}
			}
		}

		if (isset($this->fitData->data_mesgs['session']['max_heart_rate']))
			$this->TrainingObject->setPulseMax($this->fitData->data_mesgs['session']['max_heart_rate']);

		if (isset($this->fitData->data_mesgs['session']['average_heart_rate']))
			$this->TrainingObject->setPulseAvg($this->fitData->data_mesgs['session']['average_heart_rate']);

		if (isset($this->fitData->data_mesgs['session']['total_timer_time']))
			$this->TrainingObject->setTimeInSeconds($this->fitData->data_mesgs['session']['total_timer_time']);

		if (isset($this->fitData->data_mesgs['session']['total_elapsed_time']))
			$this->TrainingObject->setElapsedTime($this->fitData->data_mesgs['session']['total_elapsed_time']);

		if (isset($this->fitData->data_mesgs['session']['total_distance']))
			$this->TrainingObject->setDistance(round($this->fitData->data_mesgs['session']['total_distance'], 3));

		if (isset($this->fitData->data_mesgs['session']['total_calories']))
			$this->TrainingObject->setCalories($this->fitData->data_mesgs['session']['total_calories']);

		if ($this->isSwim) {
			if (isset($this->fitData->data_mesgs['session']['total_strokes']))
				$this->TrainingObject->setTotalStrokes($this->fitData->data_mesgs['session']['total_strokes']);

			if (isset($this->fitData->data_mesgs['session']['avg_swimming_cadence']))
				$this->TrainingObject->setCadence($this->fitData->data_mesgs['session']['avg_swimming_cadence']);

			if (isset($this->fitData->data_mesgs['session']['pool_length']))
				$this->TrainingObject->setPoolLength($this->fitData->data_mesgs['session']['pool_length']);
		} else {
			if (isset($this->fitData->data_mesgs['session']['avg_cadence']))
				$this->TrainingObject->setCadence($this->fitData->data_mesgs['session']['avg_cadence']);
		}

		if (isset($this->fitData->data_mesgs['session']['total_training_effect']) &&
		    $this->fitData->data_mesgs['session']['total_training_effect'][0] >= 10.0 &&
		    $this->fitData->data_mesgs['session']['total_training_effect'][0] <= 50.0)
			$this->TrainingObject->setFitTrainingEffect($this->fitData->data_mesgs['session']['total_training_effect']/10);

		$this->applyPauses();
		$this->setGPSarrays();
	}
}
