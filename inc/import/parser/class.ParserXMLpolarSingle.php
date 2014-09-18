<?php
/**
 * This file contains class::ParserXMLpolarSingle
 * @package Runalyze\Import\Parser
 */
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
		$this->addError('Given XML object does not contain any results. &lt;result&gt;-tag could not be located.');
	}

	/**
	 * Parse general values
	 */
	protected function parseGeneralValues() {
		$this->TrainingObject->setTimestamp( strtotime((string)$this->XML->time) );
		$this->TrainingObject->setSportid( CONF_RUNNINGSPORT );
	}

	/**
	 * Parse optional values
	 */
	protected function parseOptionalValues() {
		if (isset($this->XML->result->distance)) {
			$this->TrainingObject->setDistance( round(((double)$this->XML->result->distance)/1000, 2) );
		}

		if (isset($this->XML->result->duration)) {
			$this->TrainingObject->setTimeInSeconds( Time::toSeconds((string)$this->XML->result->duration) );
			//$parts = explode(':', (string)$this->XML->result->duration);

			//if (is_array($parts) && count($parts) == 3) {
			//	$this->TrainingObject->setTimeInSeconds(60*60*$parts[0] + 60*$parts[1] + $parts[2]);
			//}
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
				$time = Time::toSeconds((string)$Lap->duration);

				$this->TrainingObject->Splits()->addSplit($distance, $time);
			}
		}
	}

	/**
	 * Parse samples
	 */
	protected function parseSamples() {
		if (isset($this->XML->result->samples)) {
			foreach ($this->XML->result->samples->sample as $Sample) {
				switch ((string)$Sample->type) {
					case 'HEARTRATE':
						$this->TrainingObject->setArrayHeartrate( explode(',', (string)$Sample->values) );
						break;

					case 'SPEED':
						$values = array_map( array('ParserXMLpolarSingle', 'arrayMapPace'), explode(',', (string)$Sample->values) );
						$this->TrainingObject->setArrayPace( $values );
						break;

					case 'ALTITUDE':
						$this->TrainingObject->setArrayAltitude( explode(',', (string)$Sample->values) );
						break;

					case 'DISTANCE':
						$values = array_map( array('ParserXMLpolarSingle', 'arrayMapDistance'), explode(',', (string)$Sample->values) );
						$this->TrainingObject->setArrayDistance( $values );
						break;
				}
			}
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
		return round($distance/1000, 3);
	}
}