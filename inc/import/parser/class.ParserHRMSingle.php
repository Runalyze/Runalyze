<?php
/**
 * This file contains class::ParserHRMSingle
 * @package Runalyze\Import\Parser
 */
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
	 * Parse
	 */
	public function parse() {
		$separator = "\r\n";
		$this->Line = strtok($this->FileContent, $separator);

		while ($this->Line !== false) {
			if (substr($this->Line, 0, 1) == '[')
				$this->Header = substr($this->Line, 1, -1);
			else
				$this->parseLine();

			$this->Line = strtok( $separator );
		}

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
			$time = Time::toSeconds(substr($this->Line, 10));
			$this->TrainingObject->setTimestamp( $this->TrainingObject->getTimestamp() + $time );
		} elseif (substr($this->Line, 0, 6) == 'Length') {
			$this->TrainingObject->setTimeInSeconds( Time::toSeconds(substr($this->Line, 7)) );
		}
	}

	/**
	 * Read lap
	 */
	private function readLap() {
		if (strpos($this->Line, ':'))
			$this->TrainingObject->Splits()->addSplit(0, Time::toSeconds(substr($this->Line, 0, 10)));
	}

	/**
	 * Read heartrate
	 */
	private function readHRdata() {
		$values = preg_split('/[\s]+/', $this->Line);
	
		$this->gps['heartrate'][] = (int)trim($values[0]);
		$this->gps['rpm'][]       = isset($values[2]) ? (int)trim($values[2]) : 0;
	}
}