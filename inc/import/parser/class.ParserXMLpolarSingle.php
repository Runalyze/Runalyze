<?php
/**
 * This file contains class::ParserXMLpolarSingle
 * @package Runalyze\Import\Parser
 */

use Runalyze\Configuration;
use Runalyze\Activity\Duration;

/**
 * Parser for XML files from Polar
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserXMLpolarSingle extends ParserAbstractSingleXML {
	/**
	 * Parse
	 */
	protected function parseXML() {
		if ($this->isCorrectXML()) {
			$this->parseGeneralValues();
			$this->parseOptionalValues();
			$this->parseLaps();
			$this->parseSamples();
		} else {
			$this->throwNoXMLError();
		}
	}

	/**
	 * Is a correct file given?
	 * @return bool
	 */
	protected function isCorrectXML() {
		return !empty($this->XML->result);
	}

	/**
	 * Add error: incorrect file
	 */
	protected function throwNoXMLError() {
		$this->addError( __('Given XML object does not contain any results. &lt;result&gt;-tag could not be located.') );
	}

	/**
	 * Parse general values
	 */
	protected function parseGeneralValues() {
		$this->TrainingObject->setTimestamp( strtotime((string)$this->XML->time) );
		$this->TrainingObject->setSportid( Configuration::General()->runningSport() );
	}

	/**
	 * Parse optional values
	 */
	protected function parseOptionalValues() {
		if (isset($this->XML->result->distance)) {
			$this->TrainingObject->setDistance( round(((double)$this->XML->result->distance)/1000, 2) );
		}

		if (isset($this->XML->result->duration)) {
			$Time = new Duration((string)$this->XML->result->duration);
			$this->TrainingObject->setTimeInSeconds( $Time->seconds() );
		}

		if (isset($this->XML->result->calories)) {
			$this->TrainingObject->setCalories((int)$this->XML->result->calories);
		}

		if (isset($this->XML->result->{'heart-rate'})) {
			$this->TrainingObject->setPulseAvg((int)$this->XML->result->{'heart-rate'}->average);
			$this->TrainingObject->setPulseMax((int)$this->XML->result->{'heart-rate'}->maximum);
		}
	}

	/**
	 * Parse laps
	 */
	protected function parseLaps() {
		if (isset($this->XML->result->laps)) {
			foreach ($this->XML->result->laps->lap as $Lap) {
				$distance = round(((double)$Lap->distance)/1000, 2);
				$Time = new Duration((string)$Lap->duration);

				$this->TrainingObject->Splits()->addSplit($distance, $Time->seconds());
			}
		}
	}

	/**
	 * Parse samples
	 */
	protected function parseSamples() {
		$Num = 0;
		$Interval = (int)$this->XML->result->{'recording-rate'};

		if (isset($this->XML->result->samples)) {
			foreach ($this->XML->result->samples->sample as $Sample) {
				$Data = explode(',', (string)$Sample->values);

				if (end($Data) == '') {
					array_pop($Data);
				}

				$Num = count($Data);

				switch ((string)$Sample->type) {
					case 'HEARTRATE':
						$this->TrainingObject->setArrayHeartrate( $Data );
						break;

					case 'SPEED':
						$values = array_map( array('ParserXMLpolarSingle', 'arrayMapPace'), $Data );
						$this->TrainingObject->setArrayPace( $values );
						break;

					case 'ALTITUDE':
						$this->TrainingObject->setArrayAltitude( $Data );
						break;

					case 'DISTANCE':
						$values = array_map( array('ParserXMLpolarSingle', 'arrayMapDistance'), $Data );
						$this->TrainingObject->setArrayDistance( $values );
						break;

					case 'RUN_CADENCE':
						$this->TrainingObject->setArrayCadence( $Data );
						break;
				}
			}
		}

		if ($Interval > 0 && $Num > 0) {
			$this->TrainingObject->setArrayTime(range(0, ($Num - 1)*$Interval, $Interval) );
		}
	}

	/**
	 * Array map: km/h -> s/km
	 * @param double $speed
	 * @return int
	 */
	public static function arrayMapPace($speed) {
		if ($speed <= 0) {
			return 0;
		}
 
		return round(3600/$speed);
	}

	/**
	 * Array map: m -> km
	 * @param double $distance
	 * @return double
	 */
	public static function arrayMapDistance($distance) {
		return round($distance/1000, ParserAbstract::DISTANCE_PRECISION);
 	}
 }
