<?php
/**
 * This file contains class::ParserCSVepsonSingle
 * @package Runalyze\Import\Parser
 */

/**
 * Parser for csv files from Epson
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserCSVepsonSingle extends ParserAbstractSingle {
	/**
	 * @var string
	 */
	const NEWLINE = "\n";

	/**
	 * Current header
	 * @var string
	 */
	protected $Header = '';

	/**
	 * @var array
	 */
	protected $Temp = array();

	/**
	 * @var string
	 */
	protected $GraphKey = '';

	/**
	 * @var string
	 */
	protected $GraphString = '';

	/**
	 * Parse
	 */
	public function parse() {
		$line = strtok($this->FileContent, self::NEWLINE);

		while ($line !== false) {
			$line = trim($line);

			if (substr($line, 0, 1) == '[') {
				$this->Header = substr($line, 1, -1);
				$this->Temp = array();
			} else {
				$this->parseLine($line);
			}

			$line = strtok(self::NEWLINE);
		}

		$this->finishGPSData();
		$this->setGPSarrays();
	}

	/**
	 * Parse line
	 * @param string $line
	 */
	protected function parseLine($line) {
		switch ($this->Header) {
			case 'TrainingResult':
				$this->readTrainingResult($line);
				break;

			case 'TrainingData':
				$this->readTrainingData($line);
				break;

			case 'GraphData':
				$this->readGraphData($line);
				break;

			case 'GPSData':
				$this->readGPSData($line);
				break;

			case 'LapData':
				$this->readLapData($line);
				break;

			case 'TrainingSettingData':
				break;
		}
	}

	/**
	 * @param string $line
	 */
	protected function readTrainingResult($line) {
		if (substr($line, 0, 13) == 'TrainingName,') {
			$this->TrainingObject->setComment(substr($line, 13));
		} elseif (substr($line, 0, 5) == 'Memo,') {
			$this->TrainingObject->setNotes(substr($line, 5));
		} elseif (substr($line, 0, 15) == 'TrainingKindId,') {
			$this->Temp = explode(',', $line);
		} elseif (!empty($this->Temp)) {
			$values = explode(',', $line);
			$startDay = '';
			$startTime = '';

			if (count($values) == count($this->Temp)) {
				foreach ($this->Temp as $i => $label) {
					switch ($label) {
						case 'StartDay':
							$startDay = $values[$i];
							break;

						case 'StartTime':
							$startTime = $values[$i];
							break;

						case 'TrainingTime':
							$this->TrainingObject->setTimeInSeconds($values[$i]);
							break;

						case 'Distance':
							$this->TrainingObject->setDistance($values[$i]/1000);
							break;

						case 'Temperature':
							if (strlen($values[$i]) > 0) {
								$this->TrainingObject->setTemperature($values[$i]);
							}
							break;
					}
				}

				$this->Temp = array();
			}

			$this->TrainingObject->setTimestamp(strtotime($startDay.' '.$startTime));
		}
	}

	/**
	 * @param string $line
	 */
	protected function readTrainingData($line) {
		if (empty($this->Temp)) {
			$this->Temp = explode(',', $line);
		} else {
			$values = explode(',', $line);

			foreach ($this->Temp as $i => $label) {
				switch ($label) {
					case 'Calorie':
						$this->TrainingObject->setCalories($values[$i]);
						break;
				}
			}

			$this->Temp = array();
		}
	}

	/**
	 * @var string $line
	 */
	protected function readGPSData($line) {
		$parts = explode(',', $line);

		if (count($parts) == 2) {
			$this->finishGPSData();

			$this->GraphKey = $parts[0];
			$this->GraphString = $parts[1];
		} else {
			$this->GraphString .= $parts[0];
		}
	}

	/**
	 * Finish current gps data
	 */
	protected function finishGPSData() {
		if (empty($this->GraphString)) {
			return;
		}

		$values = explode(';', $this->GraphString);

		switch ($this->GraphKey) {
			case 'GpsTime':
				$this->gps['time_in_s'] = array_map(function ($value) {
					$parts = explode(':', $value);
					return 3600*$parts[0] + 60*$parts[1] + $parts[2];
				}, $values);
				break;

			case 'GpsLatitude':
				$this->gps['latitude'] = array_map(function ($value) {
					return $value / 1000000;
				}, $values);
				break;

			case 'GpsLongitude':
				$this->gps['longitude'] = array_map(function ($value) {
					return $value / 1000000;
				}, $values);
				break;
		}

		$this->GraphKey = '';
		$this->GraphString = '';
	}

	/**
	 * @param string $line
	 */
	protected function readGraphData($line) {
		$values = explode(',', $line);
		$label = array_shift($values);

		switch ($label) {
			case 'GraphAltitude':
				$this->gps['altitude'] = $values;
				break;

			case 'GraphPitch':
				$this->gps['rpm'] = array_map(function ($value) {
					return round($value / 2);
				}, $values);
				break;

			case 'GraphDistance':
				$this->gps['km'] = array_map(function ($value) {
					return $value / 1000;
				}, $values);
				break;

			case 'HeartRate':
				$this->gps['heartrate'] = $values;
				break;
		}
	}

	/**
	 * @param string $line
	 */
	protected function readLapData($line) {
		if (empty($this->Temp)) {
			$this->Temp = array(false, false);
			$labels = explode(',', $line);

			foreach ($labels as $i => $label) {
				if ($label == 'LapTime') {
					$this->Temp[0] = $i;
				} elseif ($label == 'LapDistance') {
					$this->Temp[1] = $i;
				}
			}
		} elseif ($this->Temp[0] !== false && $this->Temp[1] !== false) {
			$values = explode(',', $line);

			$this->TrainingObject->Splits()->addSplit(
				$values[$this->Temp[1]] / 1000,
				$values[$this->Temp[0]]
			);
		}
	}
}