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
			$this->parseHeartrate();
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
		if (isset($this->XML->result->distance))
			$this->TrainingObject->setDistance( round(((double)$this->XML->result->distance)/1000, 2) );

		if (isset($this->XML->result->duration)) {
			$parts = explode(':', (string)$this->XML->result->duration);

			if (is_array($parts) && count($parts) == 3)
				$this->TrainingObject->setTimeInSeconds(60*60*$parts[0] + 60*$parts[1] + $parts[2]);
		}

		if (isset($this->XML->result->calories))
			$this->TrainingObject->setCalories((int)$this->XML->result->calories);
	}

	/**
	 * Parse heartrate
	 */
	protected function parseHeartrate() {
		if (isset($this->XML->result->{'heart-rate'})) {
			$this->TrainingObject->setPulseAvg((int)$this->XML->result->{'heart-rate'}->average);
			$this->TrainingObject->setPulseMax((int)$this->XML->result->{'heart-rate'}->maximum);
		}

		// TODO: SPEED, ALTITUDE, DISTANCE (as samples)

		if (isset($this->XML->result->samples) && (string)$this->XML->result->samples->sample->type == 'HEARTRATE')
			$this->TrainingObject->setArrayHeartrate( explode(',', (string)$this->XML->result->samples->sample->values) );
	}
}